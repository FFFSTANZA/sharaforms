<?php

namespace App\Integrations\Handlers;

use App\Models\Forms\Form;
use App\Open\MentionParser;
use App\Service\Forms\FormSubmissionFormatter;
use App\Service\Forms\SubmissionUrlService;
use App\Service\Security\PublicWebhookUrl;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class MicrosoftTeamsIntegration extends AbstractIntegrationHandler
{
    public static function getValidationRules(?Form $form): array
    {
        return [
            'teams_webhook_url' => ['required', 'url'],
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
        return $this->integrationData->teams_webhook_url ?? null;
    }

    protected function shouldRun(): bool
    {
        return ! is_null($this->getWebhookUrl())
            && $this->form->workspace?->hasFeature('integrations.microsoft_teams')
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

        // Form theme color for the card accent
        $themeColor = str_replace('#', '', $this->form->color ?? '0078d4');

        // Build body elements
        $bodyElements = [
            [
                'type' => 'TextBlock',
                'text' => $messageText,
                'wrap' => true,
                'weight' => 'Bolder',
                'size' => 'Medium',
            ],
        ];

        // Submission data section
        if (Arr::get($settings, 'include_submission_data', true)) {
            $formatter = (new FormSubmissionFormatter($this->form, $this->submissionData))->outputStringsOnly();
            if (Arr::get($settings, 'include_hidden_fields_submission_data', false)) {
                $formatter->showHiddenFields();
            }

            $facts = [];
            foreach ($formatter->getFieldsWithValue() as $field) {
                $value = is_array($field['value']) ? implode(', ', $field['value']) : $field['value'];
                $facts[] = ['name' => $this->escapeHtml($field['name']), 'value' => $this->escapeHtml((string) $value)];
            }

            if (! empty($facts)) {
                $bodyElements[] = ['type' => 'FactSet', 'facts' => $facts];
            }
        }

        // Views & submissions count
        if (Arr::get($settings, 'views_submissions_count', true)) {
            $facts = [
                ['name' => '👀 Views', 'value' => (string) $this->form->views_count],
                ['name' => '🖊️ Submissions', 'value' => (string) $this->form->submissions_count],
            ];
            $bodyElements[] = ['type' => 'FactSet', 'facts' => $facts];
        }

        // Action buttons
        $actions = [];
        if (Arr::get($settings, 'link_open_form', true)) {
            $actions[] = ['type' => 'Action.OpenUrl', 'title' => '🔗 Open Form', 'url' => $this->form->share_url];
        }
        if (Arr::get($settings, 'link_edit_form', true)) {
            $actions[] = ['type' => 'Action.OpenUrl', 'title' => '✏️ Edit Form', 'url' => front_url('forms/'.$this->form->slug.'/show')];
        }
        if (Arr::get($settings, 'link_edit_submission', true) && $this->form->editable_submissions) {
            $actions[] = ['type' => 'Action.OpenUrl', 'title' => '✏️ Edit Submission', 'url' => SubmissionUrlService::buildEditUrl($this->form, $this->submissionData['submission_id'])];
        }

        if (! empty($actions)) {
            $bodyElements[] = ['type' => 'ActionSet', 'actions' => $actions];
        }

        return [
            'type' => 'message',
            'attachments' => [
                [
                    'contentType' => 'application/vnd.microsoft.card.adaptive',
                    'contentUrl' => null,
                    'content' => [
                        '$schema' => 'http://adaptivecards.io/schemas/adaptive-card.json',
                        'type' => 'AdaptiveCard',
                        'version' => '1.4',
                        'body' => $bodyElements,
                        'msteams' => [
                            'entities' => [],
                        ],
                    ],
                ],
            ],
            'themeColor' => $themeColor,
        ];
    }

    /**
     * Escape field values for safe display in Adaptive Cards (HTML-safe text).
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
