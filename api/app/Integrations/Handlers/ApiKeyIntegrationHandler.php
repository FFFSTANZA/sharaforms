<?php

namespace App\Integrations\Handlers;

use App\Events\Forms\FormSubmitted;
use App\Models\Forms\Form;
use App\Models\Integration\FormIntegration;
use App\Service\Forms\FormSubmissionFormatter;
use App\Service\Security\PublicWebhookUrl;
use Illuminate\Support\Facades\Http;

/**
 * Base class for integrations that authenticate via API key.
 *
 * Subclasses must implement:
 * - getApiKey(): the user-provided API key
 * - getBaseUrl(): base URL for the API
 * - getEndpoint(): the API endpoint path
 * - formatPayload(): transform submission data into the target API's format
 */
abstract class ApiKeyIntegrationHandler extends AbstractIntegrationHandler
{
    public function __construct(
        protected FormSubmitted $event,
        protected FormIntegration $formIntegration,
        protected array $integration
    ) {
        parent::__construct($event, $formIntegration, $integration);
    }

    abstract protected function getApiKey(): ?string;

    abstract protected function getBaseUrl(): string;

    abstract protected function getEndpoint(): string;

    abstract protected function formatPayload(): array;

    protected function getRequestHeaders(): array
    {
        return [
            'Authorization' => 'Bearer '.$this->getApiKey(),
            'Content-Type' => 'application/json',
        ];
    }

    protected function getHttpMethod(): string
    {
        return 'POST';
    }

    protected function shouldRun(): bool
    {
        return parent::shouldRun()
            && ! empty($this->getApiKey());
    }

    public function handle(): void
    {
        if (! $this->shouldRun()) {
            return;
        }

        $url = rtrim($this->getBaseUrl(), '/').'/'.ltrim($this->getEndpoint(), '/');

        // Base URLs can be user-supplied (self-hosted instances). Enforce the
        // same SSRF guard as outgoing webhooks: HTTPS, public IPs only, no
        // redirect following, resolve pinned to the validated IP.
        PublicWebhookUrl::assertSafe($url);

        $http = Http::timeout(15)
            ->withOptions(PublicWebhookUrl::requestOptions($url))
            ->withHeaders($this->getRequestHeaders());

        $response = match ($this->getHttpMethod()) {
            'POST' => $http->post($url, $this->formatPayload()),
            'PUT' => $http->put($url, $this->formatPayload()),
            'PATCH' => $http->patch($url, $this->formatPayload()),
            default => $http->post($url, $this->formatPayload()),
        };

        $response->throw();
    }

    protected function getFormattedSubmissionData(): array
    {
        $formatter = (new FormSubmissionFormatter($this->form, $this->submissionData))
            ->useSignedUrlForFiles()
            ->showHiddenFields()
            ->outputStringsOnly();

        $data = [];
        foreach ($formatter->getFieldsWithValue() as $field) {
            $data[$field['id']] = [
                'name' => $field['name'],
                'value' => $field['value'],
                'type' => $field['type'],
            ];
        }

        return $data;
    }

    /**
     * Resolve a column-mapping entry for a submission field.
     *
     * Mapping entries come from the UI and may be stored either as a plain
     * column-name string ("Email") or an object ({column_name, column_type}).
     * Field ids are checked first, then field names.
     */
    protected function mappingEntry(array $columnMapping, string $fieldId, string $fieldName): ?array
    {
        foreach ([$fieldId, $fieldName] as $key) {
            $entry = $columnMapping[$key] ?? null;

            if (is_string($entry) && $entry !== '') {
                return ['column_name' => $entry];
            }

            if (is_array($entry)) {
                return $entry;
            }
        }

        return null;
    }

    /**
     * Escape field values for safe display in HTML-aware outputs.
     */
    protected function escapeFormattedData(array $fields): array
    {
        return array_map(function (array $field) {
            $field['value'] = is_array($field['value'] ?? null)
                ? array_map([$this, 'escapeHtml'], $field['value'])
                : $this->escapeHtml((string) ($field['value'] ?? ''));

            return $field;
        }, $fields);
    }

    protected function escapeHtml(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}
