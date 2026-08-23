<?php

namespace App\Integrations\Handlers;

use App\Models\Forms\Form;
use App\Service\Forms\FormSubmissionFormatter;

class AirtableIntegration extends ApiKeyIntegrationHandler
{
    public static function getValidationRules(?Form $form): array
    {
        return [
            'api_key' => ['required', 'string'],
            'base_id' => ['required', 'string'],
            'table_id' => ['required', 'string'],
            'column_mapping' => ['nullable', 'array'],
        ];
    }

    public static function getValidationAttributes(): array
    {
        return [
            'data.api_key' => 'Airtable API Key',
            'data.base_id' => 'Airtable Base ID',
            'data.table_id' => 'Airtable Table ID',
        ];
    }

    protected function getApiKey(): ?string
    {
        return $this->integrationData->api_key ?? null;
    }

    protected function getBaseUrl(): string
    {
        return 'https://api.airtable.com/v0';
    }

    protected function getEndpoint(): string
    {
        $baseId = $this->integrationData->base_id ?? '';
        $tableId = $this->integrationData->table_id ?? '';

        return "{$baseId}/{$tableId}";
    }

    protected function formatPayload(): array
    {
        $submissionData = $this->getFormattedSubmissionData();
        $columnMapping = (array) ($this->integrationData->column_mapping ?? []);
        $fields = [];

        foreach ($submissionData as $fieldId => $field) {
            $fieldName = $field['name'];
            $value = $field['value'];

            // Use mapping if provided, otherwise use field name
            $airtableFieldName = $columnMapping[$fieldName] ?? $fieldName;

            if (is_array($value)) {
                $value = implode(', ', $value);
            }

            $fields[$airtableFieldName] = (string) $value;
        }

        return [
            'fields' => $fields,
        ];
    }
}
