<?php

namespace App\Integrations\Handlers;

use App\Events\Forms\FormSubmitted;
use App\Integrations\Notion\NotionApiClient;
use App\Models\Forms\Form;
use App\Models\Integration\FormIntegration;
use App\Service\Forms\FormSubmissionFormatter;
use Exception;
use Illuminate\Support\Facades\Log;

class NotionIntegration extends AbstractIntegrationHandler
{
    protected ?NotionApiClient $client = null;

    public function __construct(
        protected FormSubmitted $event,
        protected FormIntegration $formIntegration,
        protected array $integration
    ) {
        parent::__construct($event, $formIntegration, $integration);
    }

    private function getClient(): NotionApiClient
    {
        if (!isset($this->client)) {
            $this->client = new NotionApiClient($this->formIntegration->provider);
        }

        return $this->client;
    }

    public static function getValidationRules(?Form $form): array
    {
        return [
            'database_id' => ['required', 'string'],
        ];
    }

    public static function isOAuthRequired(): bool
    {
        return true;
    }

    public static function getValidationAttributes(): array
    {
        return [
            'oauth_id' => 'Notion Workspace',
            'data.database_id' => 'Notion Database',
        ];
    }

    public function handle(): void
    {
        if (!$this->shouldRun()) {
            return;
        }

        $databaseId = $this->getDatabaseId();
        $columns = $this->getColumns();

        Log::debug('Pushing submission to Notion', [
            'database_id' => $databaseId,
            'form_id' => $this->form->id,
        ]);

        // Format submission data into Notion properties
        $submissionFields = $this->getFormattedSubmissionData();
        $properties = NotionApiClient::formatProperties($columns, $submissionFields);

        // Add a title property if the database has one and it's not already mapped
        $this->ensureTitleProperty($properties);

        // Create the page in Notion
        $this->getClient()->createPage($databaseId, $properties);
    }

    /**
     * Get formatted submission data (field id => value).
     */
    private function getFormattedSubmissionData(): array
    {
        $formatter = (new FormSubmissionFormatter($this->form, $this->submissionData))
            ->useSignedUrlForFiles()
            ->showHiddenFields()
            ->outputStringsOnly();

        $fields = $formatter->getFieldsWithValue();

        $data = [];
        foreach ($fields as $field) {
            $data[$field['id']] = $field['value'];
        }

        return $data;
    }

    /**
     * Ensure a title property is set for the Notion page.
     * If no column maps to the title property, use the form submission ID.
     */
    private function ensureTitleProperty(array &$properties): void
    {
        // Check if any existing property is a title type
        foreach ($properties as $key => $value) {
            if (isset($value['title'])) {
                return; // Already has a title
            }
        }

        // Find the title column name from the database schema
        $titleColumn = $this->getTitleColumnName();
        if (!$titleColumn) {
            return; // No title column in the database schema — skip silently
        }

        // If no title, use the submission ID or a timestamp
        $submissionId = $this->submissionData['submission_id'] ?? null;
        $title = $submissionId ? "Submission #{$submissionId}" : 'Form Submission ' . now()->format('Y-m-d H:i:s');

        $properties[$titleColumn] = [
            'title' => [
                ['text' => ['content' => $title]],
            ],
        ];
    }

    /**
     * Get the title column name from the integration data schema.
     */
    private function getTitleColumnName(): ?string
    {
        $schema = $this->integrationData->schema ?? [];
        foreach ($schema as $prop) {
            $prop = (array) $prop;
            if (($prop['type'] ?? '') === 'title') {
                return $prop['name'];
            }
        }
        return null;
    }

    protected function getDatabaseId(): string
    {
        if (!isset($this->integrationData->database_id)) {
            throw new Exception('No Notion database selected.');
        }

        return $this->integrationData->database_id;
    }

    protected function getColumns(): array
    {
        return $this->integrationData->columns ?? [];
    }

    protected function shouldRun(): bool
    {
        return parent::shouldRun()
            && $this->form->workspace?->hasFeature('integrations.notion')
            && $this->formIntegration->oauth_id
            && isset($this->integrationData->database_id);
    }
}
