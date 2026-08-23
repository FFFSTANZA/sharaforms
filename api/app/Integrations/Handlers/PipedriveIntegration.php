<?php

namespace App\Integrations\Handlers;

use App\Models\Forms\Form;
use App\Open\MentionParser;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PipedriveIntegration extends ApiKeyIntegrationHandler
{
    /**
     * Memoized FormSubmissionFormatter output (one pass per submission).
     */
    private ?array $submissionFieldsCache = null;

    public static function getValidationRules(?Form $form): array
    {
        return [
            'api_token' => ['required', 'string'],
            'deal_title_template' => ['nullable', 'string'],
            'pipeline_id' => ['nullable', 'string'],
            'stage_id' => ['nullable', 'string'],
            'deal_value' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'person_name_field_id' => ['nullable', 'string'],
            'person_email_field_id' => ['nullable', 'string'],
            'person_phone_field_id' => ['nullable', 'string'],
            'include_submission_data' => ['boolean'],
            'include_hidden_fields_submission_data' => ['nullable', 'boolean'],
        ];
    }

    public static function getValidationAttributes(): array
    {
        return [
            'data.api_token' => 'Pipedrive API Token',
            'data.deal_title_template' => 'Deal Title',
            'data.pipeline_id' => 'Pipedrive Pipeline',
            'data.stage_id' => 'Pipedrive Stage',
            'data.deal_value' => 'Deal Value',
            'data.currency' => 'Currency',
            'data.person_name_field_id' => 'Person Name Field',
            'data.person_email_field_id' => 'Person Email Field',
            'data.person_phone_field_id' => 'Person Phone Field',
        ];
    }

    protected function getApiKey(): ?string
    {
        return $this->integrationData->api_token ?? null;
    }

    protected function getBaseUrl(): string
    {
        return 'https://api.pipedrive.com/v1';
    }

    /**
     * Pipedrive expects the token as a query parameter on every request.
     */
    protected function getEndpoint(): string
    {
        return '?api_token='.urlencode((string) $this->getApiKey());
    }

    /**
     * Creating a deal may require a person first, so the flow is:
     * 1. Create (or reuse) a person from mapped name/email fields.
     * 2. Create the deal linked to that person.
     */
    public function handle(): void
    {
        if (! $this->shouldRun()) {
            return;
        }

        $payload = $this->formatPayload();

        if (! empty($payload['person_name']) || ! empty($payload['person_email']) || ! empty($payload['person_phone'])) {
            $personId = $this->createPerson($payload);
            if ($personId) {
                unset($payload['person_name'], $payload['person_email'], $payload['person_phone']);
                $payload['person_id'] = $personId;
            }
        }

        Http::timeout(15)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($this->buildUrl('deals'), $payload)
            ->throw();
    }

    private function buildUrl(string $path): string
    {
        return rtrim($this->getBaseUrl(), '/').'/'.$path.$this->getEndpoint();
    }

    /**
     * Memoized submission fields — one formatter pass per submission.
     */
    private function submissionFields(): array
    {
        if ($this->submissionFieldsCache === null) {
            $this->submissionFieldsCache = $this->getFormattedSubmissionData();
        }

        return $this->submissionFieldsCache;
    }

    /**
     * Create a person and return its id (null when the API rejects it).
     */
    private function createPerson(array $payload): ?int
    {
        $body = [
            'name' => $payload['person_name'] ?: $payload['person_email'],
        ];

        if (! empty($payload['person_email'])) {
            $body['email'] = [$payload['person_email']];
        }
        if (! empty($payload['person_phone'])) {
            $body['phone'] = [$payload['person_phone']];
        }

        $response = Http::timeout(15)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($this->buildUrl('persons'), $body);

        $response->throw();

        $personId = data_get($response->json(), 'data.id');

        return is_numeric($personId) ? (int) $personId : null;
    }

    protected function formatPayload(): array
    {
        $settings = (array) ($this->integrationData ?? []);

        $formattedData = $this->escapeFormattedData(
            array_map(fn ($field) => [
                'name' => $field['name'],
                'value' => $field['value'],
                'type' => $field['type'],
            ], $this->submissionFields())
        );

        // Deal title: mention template or fallback to "<Form Title> - Submission"
        $title = Arr::get($settings, 'deal_title_template', '');
        if (empty($title)) {
            $title = $this->form->title.' - Submission';
        } else {
            $title = (new MentionParser($title, $formattedData, $this->getComputedValues()))->parseAsText();
        }

        $payload = [
            'title' => $title,
            'person_name' => $this->submissionFieldValue(Arr::get($settings, 'person_name_field_id')),
            'person_email' => $this->submissionFieldValue(Arr::get($settings, 'person_email_field_id')),
            'person_phone' => $this->submissionFieldValue(Arr::get($settings, 'person_phone_field_id')),
        ];

        if ($pipelineId = Arr::get($settings, 'pipeline_id')) {
            $payload['pipeline_id'] = (int) $pipelineId;
        }
        if ($stageId = Arr::get($settings, 'stage_id')) {
            $payload['stage_id'] = (int) $stageId;
        }
        if (! is_null(Arr::get($settings, 'deal_value')) && Arr::get($settings, 'deal_value') !== '') {
            $payload['value'] = (float) Arr::get($settings, 'deal_value');
        }
        if ($currency = Arr::get($settings, 'currency')) {
            $payload['currency'] = Str::upper((string) $currency);
        }

        return $payload;
    }

    /**
     * Read the submitted value of a form field by its id.
     */
    private function submissionFieldValue(?string $fieldId): ?string
    {
        if (empty($fieldId)) {
            return null;
        }

        $value = $this->submissionFields()[$fieldId]['value'] ?? null;

        if (is_null($value) || $value === '') {
            return null;
        }

        return is_array($value) ? implode(', ', $value) : (string) $value;
    }

    protected function shouldRun(): bool
    {
        return parent::shouldRun()
            && $this->form->workspace?->hasFeature('integrations.pipedrive')
            && ! empty($this->getApiKey());
    }
}
