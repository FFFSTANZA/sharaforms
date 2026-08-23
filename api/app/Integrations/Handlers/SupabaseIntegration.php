<?php

namespace App\Integrations\Handlers;

use App\Models\Forms\Form;
use App\Open\MentionParser;
use App\Service\Forms\FormSubmissionFormatter;
use App\Service\Forms\SubmissionUrlService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class SupabaseIntegration extends ApiKeyIntegrationHandler
{
    public static function getValidationRules(?Form $form): array
    {
        return [
            'api_key' => ['required', 'string'],
            'project_url' => ['required', 'url'],
            'table_name' => ['required', 'string'],
            'column_mapping' => ['nullable', 'array'],
            'message' => ['nullable', 'string'],
            'include_submission_data' => ['boolean'],
            'include_hidden_fields_submission_data' => ['nullable', 'boolean'],
            'link_open_form' => ['boolean'],
            'link_edit_form' => ['boolean'],
            'link_edit_submission' => ['boolean'],
            'views_submissions_count' => ['boolean'],
            'on_conflict' => ['nullable', 'string'],
        ];
    }

    public static function getValidationAttributes(): array
    {
        return [
            'data.api_key' => 'Supabase API Key (anon or service_role)',
            'data.project_url' => 'Supabase Project URL',
            'data.table_name' => 'Table Name',
        ];
    }

    protected function getApiKey(): ?string
    {
        return $this->integrationData->api_key ?? null;
    }

    protected function getBaseUrl(): string
    {
        $projectUrl = $this->integrationData->project_url ?? '';

        return rtrim($projectUrl, '/');
    }

    protected function getEndpoint(): string
    {
        $tableName = $this->integrationData->table_name ?? '';

        return 'rest/v1/'.$tableName;
    }

    protected function getRequestHeaders(): array
    {
        $headers = [
            'Authorization' => 'Bearer '.$this->getApiKey(),
            'apikey' => $this->getApiKey(),
            'Content-Type' => 'application/json',
            'Prefer' => 'return=minimal',
        ];

        // Upsert support
        $onConflict = $this->integrationData->on_conflict ?? null;
        if ($onConflict) {
            $headers['Prefer'] = 'resolution=merge-duplicates,return=minimal';
        }

        return $headers;
    }

    protected function formatPayload(): array
    {
        $submissionData = $this->getFormattedSubmissionData();
        // Deep-cast: integrationData->data is stdClass, and nested objects (column_mapping) stay as stdClass
        $columnMapping = json_decode(json_encode($this->integrationData->column_mapping ?? []), true);

        $row = [];

        foreach ($submissionData as $fieldId => $field) {
            $fieldName = $field['name'];
            $fieldType = $field['type'];
            $value = $field['value'];

            // Use mapping if provided, otherwise slugify the field name
            $mapping = $this->mappingEntry($columnMapping, $fieldId, $fieldName);
            $columnName = $mapping['column_name'] ?? $this->slugify($fieldName);

            // Cast value based on field type
            $row[$columnName] = $this->castValue($value, $fieldType, $mapping);
        }

        // Add metadata
        if ($this->form->editable_submissions && isset($this->submissionData['submission_id'])) {
            $row['_submission_id'] = $this->submissionData['submission_id'];
            $row['_edit_url'] = SubmissionUrlService::buildEditUrl($this->form, $this->submissionData['submission_id']);
        }

        $row['_form_id'] = $this->form->id;
        $row['_submitted_at'] = now()->toIso8601String();

        return $row;
    }

    /**
     * Cast a submission value to the appropriate type for Supabase.
     */
    private function castValue(mixed $value, string $fieldType, ?array $mapping = null): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        // If the user specified a target type in the mapping, use it
        $targetType = $mapping['column_type'] ?? null;

        if ($targetType === 'json' || $targetType === 'jsonb') {
            return is_array($value) ? $value : json_decode($value, true) ?? $value;
        }

        if ($targetType === 'int' || $targetType === 'integer' || $targetType === 'bigint') {
            return (int) $value;
        }

        if ($targetType === 'float' || $targetType === 'numeric' || $targetType === 'decimal') {
            return (float) $value;
        }

        if ($targetType === 'bool' || $targetType === 'boolean') {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        if ($targetType === 'date') {
            return $value;
        }

        if ($targetType === 'timestamptz' || $targetType === 'timestamp') {
            return $value;
        }

        // Array types
        if ($targetType === '_text' || $targetType === 'text[]' || $fieldType === 'checkbox') {
            return is_array($value) ? $value : explode(', ', $value);
        }

        // Default: return as string
        return is_array($value) ? implode(', ', $value) : (string) $value;
    }

    private function slugify(string $text): string
    {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', '_', $text);
        $text = trim($text, '_');

        return $text;
    }

    protected function shouldRun(): bool
    {
        return parent::shouldRun()
            && $this->form->workspace?->hasFeature('integrations.supabase')
            && ! empty($this->integrationData->project_url)
            && ! empty($this->integrationData->table_name);
    }
}
