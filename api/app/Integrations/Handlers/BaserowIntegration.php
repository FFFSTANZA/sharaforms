<?php

namespace App\Integrations\Handlers;

use App\Models\Forms\Form;

class BaserowIntegration extends ApiKeyIntegrationHandler
{
    public const DEFAULT_BASE_URL = 'https://api.baserow.io';

    public static function getValidationRules(?Form $form): array
    {
        return [
            'api_key' => ['required', 'string'],
            'base_url' => ['nullable', 'url'],
            'workspace_id' => ['nullable', 'string'],
            'database_id' => ['nullable', 'string'],
            'table_id' => ['required', 'string'],
            'column_mapping' => ['nullable', 'array'],
        ];
    }

    public static function getValidationAttributes(): array
    {
        return [
            'data.api_key' => 'Baserow API Token',
            'data.base_url' => 'Baserow Instance URL',
            'data.table_id' => 'Baserow Table',
        ];
    }

    protected function getApiKey(): ?string
    {
        return $this->integrationData->api_key ?? null;
    }

    /**
     * Baserow authenticates with the raw token in the Authorization header,
     * not a Bearer prefix.
     */
    protected function getRequestHeaders(): array
    {
        return [
            'Authorization' => 'Token '.$this->getApiKey(),
            'Content-Type' => 'application/json',
        ];
    }

    protected function getBaseUrl(): string
    {
        $baseUrl = trim((string) ($this->integrationData->base_url ?? ''));

        return $baseUrl !== '' ? rtrim($baseUrl, '/') : self::DEFAULT_BASE_URL;
    }

    protected function getEndpoint(): string
    {
        $tableId = $this->integrationData->table_id ?? '';

        // user_field_names=true lets us address columns by their human names.
        return 'api/database/rows/table/'.$tableId.'/?user_field_names=true';
    }

    protected function formatPayload(): array
    {
        $submissionData = $this->getFormattedSubmissionData();
        $columnMapping = json_decode(json_encode($this->integrationData->column_mapping ?? []), true);
        $row = [];

        foreach ($submissionData as $fieldId => $field) {
            $fieldName = $field['name'];
            $value = $field['value'];

            // Use mapping if provided, otherwise slugify the field name
            $mapping = $this->mappingEntry($columnMapping, $fieldId, $fieldName);
            $columnName = $mapping['column_name'] ?? $this->slugify($fieldName);

            $row[$columnName] = $this->castValue($value, $mapping);
        }

        return $row;
    }

    /**
     * Cast a submission value based on the target Baserow field type chosen
     * in the column mapping. Unmapped values are sent as strings.
     */
    private function castValue(mixed $value, ?array $mapping): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        $targetType = $mapping['column_type'] ?? null;

        switch ($targetType) {
            case 'int':
            case 'integer':
            case 'bigint':
                return (int) $value;
            case 'float':
            case 'numeric':
            case 'decimal':
                return (float) $value;
            case 'bool':
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'json':
                return is_array($value) ? $value : json_decode($value, true) ?? $value;
            case '_text':
            case 'text[]':
                return is_array($value) ? $value : explode(', ', $value);
            default:
                return is_array($value) ? implode(', ', $value) : (string) $value;
        }
    }

    private function slugify(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9]+/', '_', $text);
        $text = trim($text, '_');

        return $text;
    }

    protected function shouldRun(): bool
    {
        return parent::shouldRun()
            && $this->form->workspace?->hasFeature('integrations.baserow')
            && ! empty($this->getApiKey())
            && ! empty($this->integrationData->table_id);
    }
}
