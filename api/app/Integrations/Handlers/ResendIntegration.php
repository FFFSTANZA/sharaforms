<?php

namespace App\Integrations\Handlers;

use App\Models\Forms\Form;
use App\Open\MentionParser;
use App\Service\Forms\FormSubmissionFormatter;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class ResendIntegration extends ApiKeyIntegrationHandler
{
    /**
     * Resend rejects sends with more than 50 total recipients.
     */
    public const MAX_RECIPIENTS = 50;

    /**
     * Memoized FormSubmissionFormatter output (one pass per submission).
     */
    private ?array $submissionFieldsCache = null;

    /**
     * Memoized formatter output without logic-hidden fields.
     */
    private ?array $visibleSubmissionFieldsCache = null;

    /**
     * Matches "user@example.com" or "Name <user@example.com>".
     */
    public const EMAIL_ADDRESS_PATTERN = '/^\s*(?:[^<>,]+<)?[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}>\?\s*$/';

    public static function getValidationRules(?Form $form): array
    {
        return [
            'api_key' => ['required', 'string'],
            'from' => ['required', 'string', 'max:320', 'regex:/^[^<>,]*<[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}>$|^[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$/'],
            'to' => [
                'required', 'string', 'max:2000',
                function ($attribute, $value, $fail) {
                    // Resend rejects sends with more than 50 total recipients.
                    $total = 0;
                    foreach (['to', 'cc', 'bcc'] as $field) {
                        $raw = (string) request()->input('data.'.$field, '');
                        $parts = preg_split('/[,\n\r]+/', $raw) ?: [];
                        $total += count(array_filter(array_map('trim', $parts)));
                    }

                    if ($total > self::MAX_RECIPIENTS) {
                        $fail('Resend allows at most '.self::MAX_RECIPIENTS.' recipients per email (To + Cc + Bcc combined).');
                    }
                },
            ],
            'cc' => ['nullable', 'string', 'max:2000'],
            'bcc' => ['nullable', 'string', 'max:2000'],
            'subject' => ['required', 'string', 'max:998'],
            'body_template' => ['nullable', 'string'],
            'reply_to' => ['nullable', 'string', 'max:2000'],
            'include_submission_data' => ['boolean'],
            'include_hidden_fields_submission_data' => ['nullable', 'boolean'],
        ];
    }

    public static function getValidationAttributes(): array
    {
        return [
            'data.api_key' => 'Resend API Key',
            'data.from' => 'From Address',
            'data.to' => 'To Addresses',
            'data.subject' => 'Email Subject',
            'data.body_template' => 'Email Content',
            'data.reply_to' => 'Reply-To Address',
        ];
    }

    protected function getApiKey(): ?string
    {
        return $this->integrationData->api_key ?? null;
    }

    protected function getBaseUrl(): string
    {
        return 'https://api.resend.com';
    }

    protected function getEndpoint(): string
    {
        return 'emails';
    }

    /**
     * Split a comma/newline separated address list into clean recipients.
     */
    private function parseAddresses(string $raw): array
    {
        $parts = preg_split('/[,\n\r]+/', $raw) ?: [];

        return array_values(array_filter(array_map('trim', $parts)));
    }

    /**
     * Memoized submission fields including logic-hidden ones —
     * used for @mention resolution (one formatter pass per submission).
     */
    private function submissionFields(): array
    {
        if ($this->submissionFieldsCache === null) {
            $this->submissionFieldsCache = (new FormSubmissionFormatter($this->form, $this->submissionData))
                ->outputStringsOnly()
                ->showHiddenFields()
                ->getFieldsWithValue();
        }

        return $this->submissionFieldsCache;
    }

    /**
     * Memoized submission fields excluding logic-hidden ones —
     * used for the auto-generated summary table.
     */
    private function visibleSubmissionFields(): array
    {
        if ($this->visibleSubmissionFieldsCache === null) {
            $this->visibleSubmissionFieldsCache = (new FormSubmissionFormatter($this->form, $this->submissionData))
                ->outputStringsOnly()
                ->getFieldsWithValue();
        }

        return $this->visibleSubmissionFieldsCache;
    }

    /**
     * Render the email body. With a mention template, it is used as the content
     * and the submission table is optionally appended; without one, a full
     * auto-generated summary is sent.
     */
    private function renderBody(): string
    {
        $settings = (array) ($this->integrationData ?? []);
        $template = trim((string) Arr::get($settings, 'body_template', ''));
        $includeSubmissionData = Arr::get($settings, 'include_submission_data', true);

        if ($template !== '') {
            // parse() resolves mentions while preserving the template's HTML markup.
            $content = (new MentionParser($template, $this->submissionFields(), $this->getComputedValues()))->parse();

            if (! $includeSubmissionData) {
                return $content;
            }

            return $content.$this->buildSubmissionTable($settings, false);
        }

        return $this->buildSubmissionTable($settings, true);
    }

    /**
     * Build an HTML table of submitted answers, optionally prefixed by an intro line.
     */
    private function buildSubmissionTable(array $settings, bool $withIntro): string
    {
        $fields = Arr::get($settings, 'include_hidden_fields_submission_data', false)
            ? $this->submissionFields()
            : $this->visibleSubmissionFields();

        $rows = '';
        foreach ($fields as $field) {
            $value = is_array($field['value']) ? implode(', ', $field['value']) : $field['value'];
            $name = $this->escapeHtml($field['name']);
            $value = $this->escapeHtml((string) $value);
            $rows .= "<tr><td style=\"padding:6px 12px;font-weight:600;vertical-align:top;\">{$name}</td><td style=\"padding:6px 12px;\">{$value}</td></tr>";
        }

        $table = "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"border-collapse:collapse;\">{$rows}</table>";

        if (! $withIntro) {
            return $table;
        }

        $formTitle = $this->escapeHtml($this->form->title);

        return "<p>A new submission was received for <strong>{$formTitle}</strong>.</p>".$table;
    }

    protected function formatPayload(): array
    {
        $settings = (array) ($this->integrationData ?? []);

        $payload = [
            'from' => trim((string) Arr::get($settings, 'from')),
            'to' => $this->parseAddresses((string) Arr::get($settings, 'to', '')),
            'subject' => (new MentionParser(
                (string) Arr::get($settings, 'subject'),
                $this->escapeFormattedData($this->submissionFields()),
                $this->getComputedValues()
            ))->parseAsText(),
            'html' => $this->renderBody(),
        ];

        foreach (['cc', 'bcc'] as $listField) {
            if ($list = trim((string) Arr::get($settings, $listField, ''))) {
                $payload[$listField] = $this->parseAddresses($list);
            }
        }

        if ($replyTo = trim((string) Arr::get($settings, 'reply_to', ''))) {
            $payload['reply_to'] = $this->parseAddresses($replyTo);
        }

        return $payload;
    }

    protected function shouldRun(): bool
    {
        return parent::shouldRun()
            && $this->form->workspace?->hasFeature('integrations.resend')
            && ! empty($this->getApiKey())
            && ! empty($this->integrationData->from)
            && ! empty($this->integrationData->to)
            && ! empty($this->integrationData->subject);
    }
}
