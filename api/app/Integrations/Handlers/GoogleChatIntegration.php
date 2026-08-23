<?php

namespace App\Integrations\Handlers;

use App\Models\Forms\Form;
use App\Open\MentionParser;
use App\Service\Forms\FormSubmissionFormatter;
use App\Service\Forms\SubmissionUrlService;
use App\Service\Security\PublicWebhookUrl;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class GoogleChatIntegration extends AbstractIntegrationHandler
{
    public static function getValidationRules(?Form $form): array
    {
        return [
            'gchat_webhook_url' => ['required', 'url'],
            'message' => ['nullable', 'string'],
            'include_submission_data' => ['boolean'],
            'include_hidden_fields_submission_data' => ['nullable', 'boolean'],
            'link_open_form' => ['boolean'],
            'link_edit_form' => ['boolean'],
            'link_edit_submission' => ['boolean'],
            'views_submissions_count' => ['boolean'],
        ];
    }

    protected function getWebhookUrl(): ?string
    {
        return $this->integrationData->gchat_webhook_url ?? null;
    }

    protected function shouldRun(): bool
    {
        return ! is_null($this->getWebhookUrl())
            && $this->form->workspace?->hasFeature('integrations.google_chat')
            && parent::shouldRun();
    }

    public function handle(): void
    {
        if (! $this->shouldRun()) {
            return;
        }

        // Webhook URL is user-supplied: enforce the SSRF guard (HTTPS, public IPs).
        $url = $this->getWebhookUrl();
        PublicWebhookUrl::assertSafe($url);

        Http::timeout(10)
            ->withOptions(PublicWebhookUrl::requestOptions($url))
            ->throw()
            ->post($url, $this->getWebhookData());
    }

    protected function getWebhookData(): array
    {
        $settings = (array) ($this->integrationData ?? []);

        $formattedData = $this->escapeFormattedData(
            (new FormSubmissionFormatter($this->form, $this->submissionData))
                ->outputStringsOnly()
                ->showHiddenFields()
                ->getFieldsWithValue()
        );

        $message = Arr::get($settings, 'message', 'New form submission');
        $messageText = (new MentionParser($message, $formattedData, $this->getComputedValues()))->parseAsText();

        $sections = [];

        // Submission data section
        if (Arr::get($settings, 'include_submission_data', true)) {
            $formatter = (new FormSubmissionFormatter($this->form, $this->submissionData))->outputStringsOnly();
            if (Arr::get($settings, 'include_hidden_fields_submission_data', false)) {
                $formatter->showHiddenFields();
            }

            $widgets = [];
            foreach ($formatter->getFieldsWithValue() as $field) {
                $value = is_array($field['value']) ? implode(', ', $field['value']) : $field['value'];
                $widgets[] = [
                    'keyValue' => [
                        'topLabel' => $this->escapeHtml($field['name']),
                        'content' => $this->escapeHtml((string) $value),
                    ],
                ];
            }

            if (! empty($widgets)) {
                $sections[] = [
                    'header' => 'Submission Data',
                    'widgets' => $widgets,
                ];
            }
        }

        // Views & submissions count
        if (Arr::get($settings, 'views_submissions_count', true)) {
            $sections[] = [
                'header' => 'Form Analytics',
                'widgets' => [
                    [
                        'keyValue' => [
                            'topLabel' => '👀 Views',
                            'content' => (string) $this->form->views_count,
                        ],
                    ],
                    [
                        'keyValue' => [
                            'topLabel' => '🖊️ Submissions',
                            'content' => (string) $this->form->submissions_count,
                        ],
                    ],
                ],
            ];
        }

        // Action buttons
        $buttons = [];
        if (Arr::get($settings, 'link_open_form', true)) {
            $buttons[] = ['textButton' => ['text' => '🔗 Open Form', 'onClick' => ['openLink' => ['url' => $this->form->share_url]]]];
        }
        if (Arr::get($settings, 'link_edit_form', true)) {
            $buttons[] = ['textButton' => ['text' => '✏️ Edit Form', 'onClick' => ['openLink' => ['url' => front_url('forms/'.$this->form->slug.'/show')]]]];
        }
        if (Arr::get($settings, 'link_edit_submission', true) && $this->form->editable_submissions) {
            $buttons[] = ['textButton' => ['text' => '✏️ Edit Submission', 'onClick' => ['openLink' => ['url' => SubmissionUrlService::buildEditUrl($this->form, $this->submissionData['submission_id'])]]]];
        }

        if (! empty($buttons)) {
            $sections[] = ['widgets' => [['buttonList' => ['buttons' => $buttons]]]];
        }

        return [
            'text' => $messageText,
            'cardsV2' => [
                [
                    'cardId' => 'sharaforms-submission',
                    'card' => [
                        'header' => [
                            'title' => $this->form->title,
                            'subtitle' => 'New form submission received',
                        ],
                        'sections' => $sections,
                    ],
                ],
            ],
        ];
    }

    /**
     * Escape field values for safe display in Google Chat cards.
     */
    private function escapeFormattedData(array $fields): array
    {
        return array_map(function (array $field) {
            $field['value'] = is_array($field['value'] ?? null)
                ? array_map([$this, 'escapeHtml'], $field['value'])
                : $this->escapeHtml((string) ($field['value'] ?? ''));

            return $field;
        }, $fields);
    }

    private function escapeHtml(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}
