<?php

namespace App\Integrations\Handlers;

use App\Models\Forms\Form;
use App\Open\MentionParser;
use App\Service\Forms\FormSubmissionFormatter;
use App\Service\Security\PublicWebhookUrl;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class NtfyIntegration extends AbstractIntegrationHandler
{
    public static function getValidationRules(?Form $form): array
    {
        return [
            'ntfy_topic_url' => ['required', 'url'],
            'message' => ['nullable', 'string'],
            'include_submission_data' => ['boolean'],
            'include_hidden_fields_submission_data' => ['nullable', 'boolean'],
            'priority' => ['nullable', 'integer', 'min:1', 'max:5'],
            'tags' => ['nullable', 'string'],
            'click_url' => ['nullable', 'url'],
        ];
    }

    protected function getWebhookUrl(): ?string
    {
        $topicUrl = $this->integrationData->ntfy_topic_url ?? null;
        if (! $topicUrl) {
            return null;
        }

        return rtrim($topicUrl, '/');
    }

    protected function shouldRun(): bool
    {
        return ! is_null($this->getWebhookUrl())
            && parent::shouldRun();
    }

    public function handle(): void
    {
        if (! $this->shouldRun()) {
            return;
        }

        $settings = (array) ($this->integrationData ?? []);

        $headers = [];
        $priority = Arr::get($settings, 'priority');
        if ($priority) {
            $headers['Priority'] = (string) $priority;
        }

        $tags = Arr::get($settings, 'tags');
        if ($tags) {
            $headers['Tags'] = $tags;
        }

        $clickUrl = Arr::get($settings, 'click_url');
        if ($clickUrl) {
            $headers['Click'] = $clickUrl;
        } else {
            // Default click URL to the form's public page
            $headers['Click'] = $this->form->share_url;
        }

        // Topic URL is user-supplied: enforce the SSRF guard (HTTPS, public IPs).
        $url = $this->getWebhookUrl();
        PublicWebhookUrl::assertSafe($url);

        Http::timeout(10)
            ->withOptions(PublicWebhookUrl::requestOptions($url))
            ->withHeaders($headers)
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
        $body = (new MentionParser($message, $formattedData, $this->getComputedValues()))->parseAsText();

        if (Arr::get($settings, 'include_submission_data', true)) {
            $formatter = (new FormSubmissionFormatter($this->form, $this->submissionData))->outputStringsOnly();
            if (Arr::get($settings, 'include_hidden_fields_submission_data', false)) {
                $formatter->showHiddenFields();
            }

            $body .= "\n\n";
            foreach ($formatter->getFieldsWithValue() as $field) {
                $value = is_array($field['value']) ? implode(', ', $field['value']) : $field['value'];
                $body .= "{$field['name']}: {$value}\n";
            }
        }

        // Default tags
        $tags = ['sharaforms', 'form'];

        $data = [
            'topic' => $this->getTopicFromUrl(),
            'message' => trim($body),
            'title' => $this->form->title,
            'tags' => $tags,
        ];

        return $data;
    }

    private function getTopicFromUrl(): string
    {
        $url = $this->getWebhookUrl();
        $path = parse_url($url, PHP_URL_PATH);

        return ltrim($path, '/');
    }

    /**
     * Escape field values for safe display in ntfy messages.
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
