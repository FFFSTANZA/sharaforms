<?php

namespace App\Integrations\Notion;

use App\Models\OAuthProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Exception;

class NotionApiClient
{
    private const BASE_URL = 'https://api.notion.com/v1';
    private const API_VERSION = '2022-06-28';

    private string $accessToken;

    /** @var array<string, array> In-memory cache for database responses keyed by ID. */
    private array $databaseCache = [];

    public function __construct(
        protected OAuthProvider $provider
    ) {
        $this->accessToken = $provider->access_token;
    }

    /**
     * List all databases the integration has access to.
     */
    public function listDatabases(): array
    {
        $response = $this->request('POST', '/search', [
            'filter' => ['value' => 'database', 'property' => 'object'],
            'page_size' => 100,
        ]);

        $results = $response['results'] ?? [];

        return array_map(fn (array $db) => [
            'id' => $db['id'],
            'title' => $this->extractPlainText($db['title'] ?? []),
            'icon' => $db['icon']['emoji'] ?? null,
            'url' => $db['url'] ?? null,
            'last_edited_time' => $db['last_edited_time'] ?? null,
        ], $results);
    }

    /**
     * Get database schema (properties/columns). Results are cached per instance.
     */
    public function getDatabase(string $databaseId): array
    {
        $this->validateDatabaseId($databaseId);

        if (!isset($this->databaseCache[$databaseId])) {
            $this->databaseCache[$databaseId] = $this->request('GET', "/databases/{$databaseId}");
        }

        return $this->databaseCache[$databaseId];
    }

    /**
     * Get the property names of a database.
     */
    public function getDatabaseProperties(string $databaseId): array
    {
        $db = $this->getDatabase($databaseId);

        $properties = [];
        foreach ($db['properties'] ?? [] as $name => $prop) {
            $properties[] = [
                'name' => $name,
                'type' => $prop['type'],
                'id' => $prop['id'],
            ];
        }

        return $properties;
    }

    /**
     * Create a new page (row) in a Notion database.
     */
    public function createPage(string $databaseId, array $properties): array
    {
        $this->validateDatabaseId($databaseId);

        return $this->request('POST', '/pages', [
            'parent' => ['database_id' => $databaseId],
            'properties' => $properties,
        ]);
    }

    /**
     * Validate that a database ID is a valid UUID format.
     * Notion IDs are UUIDs (with or without hyphens).
     */
    private function validateDatabaseId(string $id): void
    {
        $uuidPattern = '/^[0-9a-f]{8}-?[0-9a-f]{4}-?[0-9a-f]{4}-?[0-9a-f]{4}-?[0-9a-f]{12}$/i';
        if (!preg_match($uuidPattern, $id)) {
            throw new Exception("Invalid Notion database ID format: {$id}");
        }
    }

    /**
     * Format a form submission as Notion page properties.
     *
     * Maps form field values to Notion database properties based on column config.
     */
    public static function formatProperties(array $columns, array $submissionFields): array
    {
        $properties = [];

        foreach ($columns as $column) {
            $column = (array) $column;
            $fieldName = $column['notion_property'] ?? $column['name'];
            $fieldId = $column['id'];
            $value = $submissionFields[$fieldId] ?? null;

            if ($value === null || $value === '') {
                continue;
            }

            $propertyType = $column['notion_type'] ?? 'rich_text';
            $formatted = self::formatPropertyValue($propertyType, $value);

            // formatPropertyValue returns null for values that cannot be
            // represented in the target Notion type (e.g. non-numeric → number).
            if ($formatted !== null) {
                $properties[$fieldName] = $formatted;
            }
        }

        return $properties;
    }

    /**
     * Format a value for a specific Notion property type.
     * Returns null when the value cannot be represented in the target type.
     */
    private static function formatPropertyValue(string $type, mixed $value): ?array
    {
        $stringValue = is_array($value) ? implode(', ', $value) : (string) $value;

        return match ($type) {
            'title' => [
                'title' => [
                    ['text' => ['content' => mb_substr($stringValue, 0, 2000)]],
                ],
            ],
            'rich_text' => [
                'rich_text' => [
                    ['text' => ['content' => mb_substr($stringValue, 0, 2000)]],
                ],
            ],
            'number' => is_numeric($stringValue)
                ? ['number' => (float) $stringValue]
                : null,
            'email' => [
                'email' => $stringValue,
            ],
            'url' => [
                'url' => $stringValue,
            ],
            'date' => $stringValue
                ? ['date' => ['start' => $stringValue]]
                : null,
            'checkbox' => [
                'checkbox' => in_array(strtolower($stringValue), ['yes', 'true', '1', 'on']),
            ],
            'select' => [
                'select' => ['name' => $stringValue],
            ],
            'multi_select' => [
                'multi_select' => array_map(
                    fn (string $val) => ['name' => trim($val)],
                    explode(',', $stringValue)
                ),
            ],
            default => [
                'rich_text' => [
                    ['text' => ['content' => mb_substr($stringValue, 0, 2000)]],
                ],
            ],
        };
    }

    /**
     * Extract plain text from Notion rich text array.
     */
    private function extractPlainText(array $richText): string
    {
        return collect($richText)
            ->map(fn ($item) => $item['plain_text'] ?? '')
            ->implode('');
    }

    /**
     * Make an authenticated request to the Notion API.
     */
    private function request(string $method, string $endpoint, ?array $body = null): array
    {
        $url = self::BASE_URL . $endpoint;

        $http = Http::timeout(15)->withHeaders([
            'Authorization' => 'Bearer ' . $this->accessToken,
            'Notion-Version' => self::API_VERSION,
        ]);

        /** @var Response $response */
        $response = match ($method) {
            'GET' => $http->get($url),
            'POST' => $http->post($url, $body),
            default => throw new Exception("Unsupported HTTP method: {$method}"),
        };

        if ($response->failed()) {
            $error = $response->json();
            throw new Exception(
                'Notion API error: ' . ($error['message'] ?? $response->body())
            );
        }

        return $response->json() ?? [];
    }
}
