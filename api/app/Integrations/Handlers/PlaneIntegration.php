<?php

namespace App\Integrations\Handlers;

use App\Models\Forms\Form;
use App\Open\MentionParser;
use App\Service\Forms\FormSubmissionFormatter;
use App\Service\Forms\SubmissionUrlService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class PlaneIntegration extends ApiKeyIntegrationHandler
{
    public const DEFAULT_BASE_URL = 'https://api.plane.so';

    public const PRIORITIES = ['urgent', 'high', 'medium', 'low', 'none'];

    public static function getValidationRules(?Form $form): array
    {
        return [
            'api_key' => ['required', 'string'],
            'base_url' => ['nullable', 'url'],
            'workspace_slug' => ['required', 'string'],
            'project_id' => ['required', 'string'],
            'state_id' => ['nullable', 'string'],
            'priority' => ['nullable', 'string', 'in:'.implode(',', self::PRIORITIES)],
            'issue_title_template' => ['nullable', 'string'],
            'description_template' => ['nullable', 'string'],
            'include_submission_data' => ['boolean'],
            'include_hidden_fields_submission_data' => ['nullable', 'boolean'],
            'link_open_form' => ['boolean'],
            'link_edit_form' => ['boolean'],
            'link_edit_submission' => ['boolean'],
        ];
    }

    public static function getValidationAttributes(): array
    {
        return [
            'data.api_key' => 'Plane API Key',
            'data.base_url' => 'Plane Instance URL',
            'data.workspace_slug' => 'Plane Workspace',
            'data.project_id' => 'Plane Project',
            'data.state_id' => 'Plane State',
            'data.priority' => 'Priority',
            'data.issue_title_template' => 'Issue Title',
            'data.description_template' => 'Issue Description Template',
        ];
    }

    protected function getApiKey(): ?string
    {
        return $this->integrationData->api_key ?? null;
    }

    /**
     * Plane authenticates with the X-API-Key header.
     */
    protected function getRequestHeaders(): array
    {
        return [
            'X-API-Key' => (string) $this->getApiKey(),
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
        $workspaceSlug = $this->integrationData->workspace_slug ?? '';
        $projectId = $this->integrationData->project_id ?? '';

        return "api/v1/workspaces/{$workspaceSlug}/projects/{$projectId}/issues/";
    }

    protected function formatPayload(): array
    {
        $settings = (array) ($this->integrationData ?? []);

        $formattedData = $this->escapeFormattedData(
            (new FormSubmissionFormatter($this->form, $this->submissionData))
                ->outputStringsOnly()
                ->showHiddenFields()
                ->getFieldsWithValue()
        );

        // Issue title: mention template or fallback to "<Form Title> - Submission"
        $title = Arr::get($settings, 'issue_title_template', '');
        if (empty($title)) {
            $title = $this->form->title.' - Submission';
        } else {
            $title = (new MentionParser($title, $formattedData, $this->getComputedValues()))->parseAsText();
        }

        $payload = [
            'name' => $title,
            'description_html' => '<p>'.nl2br($this->escapeHtml($this->buildDescription($settings, $formattedData))).'</p>',
        ];

        if ($stateId = Arr::get($settings, 'state_id')) {
            $payload['state_id'] = $stateId;
        }
        if ($priority = Arr::get($settings, 'priority')) {
            $payload['priority'] = $priority;
        }

        return $payload;
    }

    /**
     * Build a plain-text issue description (embedded into description_html)
     * with optional submission data and links.
     */
    private function buildDescription(array $settings, array $formattedData): string
    {
        $parts = [];

        $template = Arr::get($settings, 'description_template');
        if ($template) {
            $parts[] = (new MentionParser($template, $formattedData, $this->getComputedValues()))->parseAsText();
        }

        if (Arr::get($settings, 'include_submission_data', true)) {
            $formatter = (new FormSubmissionFormatter($this->form, $this->submissionData))->outputStringsOnly();
            if (Arr::get($settings, 'include_hidden_fields_submission_data', false)) {
                $formatter->showHiddenFields();
            }

            $fieldLines = [];
            foreach ($formatter->getFieldsWithValue() as $field) {
                $value = is_array($field['value']) ? implode(', ', $field['value']) : $field['value'];
                $fieldLines[] = "{$field['name']}: {$value}";
            }

            if (! empty($fieldLines)) {
                $parts[] = implode("\n", $fieldLines);
            }
        }

        $links = [];
        if (Arr::get($settings, 'link_open_form', true)) {
            $links[] = "Open Form: {$this->form->share_url}";
        }
        if (Arr::get($settings, 'link_edit_form', true)) {
            $links[] = 'Edit Form: '.front_url('forms/'.$this->form->slug.'/show');
        }
        if (Arr::get($settings, 'link_edit_submission', true) && $this->form->editable_submissions && isset($this->submissionData['submission_id'])) {
            $links[] = 'Edit Submission: '.SubmissionUrlService::buildEditUrl($this->form, $this->submissionData['submission_id']);
        }

        if (! empty($links)) {
            $parts[] = implode("\n", $links);
        }

        return implode("\n\n", array_filter($parts));
    }

    protected function shouldRun(): bool
    {
        return parent::shouldRun()
            && $this->form->workspace?->hasFeature('integrations.plane')
            && ! empty($this->getApiKey())
            && ! empty($this->integrationData->workspace_slug)
            && ! empty($this->integrationData->project_id);
    }
}
