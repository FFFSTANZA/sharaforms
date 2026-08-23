<?php

namespace App\Integrations\Handlers\Events;

use App\Integrations\Notion\NotionApiClient;
use App\Models\Integration\FormIntegration;
use Illuminate\Support\Facades\Log;

class NotionIntegrationCreated extends AbstractIntegrationCreated
{
    public function handle(): void
    {
        $provider = $this->formIntegration->provider;

        if (!$provider) {
            Log::warning('Notion integration created without an OAuth provider', [
                'integration_id' => $this->formIntegration->id,
            ]);
            return;
        }

        try {
            $client = new NotionApiClient($provider);
            $databaseId = $this->formIntegration->data->database_id ?? null;

            if (!$databaseId) {
                return; // No database selected yet; will be configured later
            }

            // Fetch the database schema and store it
            $properties = $client->getDatabaseProperties($databaseId);

            // Get the database URL for the user
            $database = $client->getDatabase($databaseId);
            $databaseUrl = $database['url'] ?? null;

            // Build columns from form properties, mapped to Notion properties
            $columns = $this->buildColumns($properties);

            $this->formIntegration->update([
                'data' => array_merge((array) $this->formIntegration->data, [
                    'columns' => $columns,
                    'schema' => $properties,
                    'database_url' => $databaseUrl,
                ]),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to initialize Notion integration', [
                'integration_id' => $this->formIntegration->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Build column mapping from form properties to Notion database properties.
     * Auto-matches by name when possible.
     */
    private function buildColumns(array $notionProperties): array
    {
        $form = $this->formIntegration->form;
        $columns = [];

        foreach ($form->properties as $property) {
            $fieldType = $property['type'] ?? '';
            if (str_starts_with($fieldType, 'nf-')) {
                continue;
            }

            // Try to find a matching Notion property by name
            $matchedProperty = $this->findMatchingProperty($property['name'], $notionProperties);

            $column = [
                'id' => $property['id'],
                'name' => $property['name'],
                'notion_property' => $matchedProperty ? $matchedProperty['name'] : $property['name'],
                'notion_type' => $matchedProperty ? $matchedProperty['type'] : 'rich_text',
            ];

            $columns[] = $column;
        }

        return $columns;
    }

    /**
     * Find a Notion property that matches the form field name.
     */
    private function findMatchingProperty(string $fieldName, array $notionProperties): ?array
    {
        $normalizedFieldName = strtolower(trim($fieldName));

        foreach ($notionProperties as $prop) {
            if (strtolower(trim($prop['name'])) === $normalizedFieldName) {
                return $prop;
            }
        }

        return null;
    }
}
