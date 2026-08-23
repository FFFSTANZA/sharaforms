<?php

namespace App\Integrations\Handlers;

use App\Models\Forms\Form;
use App\Open\MentionParser;
use App\Service\Forms\FormSubmissionFormatter;
use App\Service\Forms\SubmissionUrlService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class LinearIntegration extends ApiKeyIntegrationHandler
{
    public const PRIORITIES = [
        ['value' => 0, 'label' => 'No priority'],
        ['value' => 1, 'label' => 'Urgent'],
        ['value' => 2, 'label' => 'High'],
        ['value' => 3, 'label' => 'Normal'],
        ['value' => 4, 'label' => 'Low'],
    ];

    public static function getValidationRules(?Form $form): array
    {
        return [
            'api_key' => ['required', 'string'],
            'team_id' => ['required', 'string'],
            'project_id' => ['nullable', 'string'],
            'state_id' => ['nullable', 'string'],
            'title_template' => ['nullable', 'string'],
            'description_template' => ['nullable', 'string'],
            'priority' => ['nullable', 'integer', 'min:0', 'max:4'],
            'label_ids' => ['nullable', 'string'],
            'include_submission_data' => ['boolean'],
            'include_hidden_fields_submission_data' => ['nullable', 'boolean'],
            'link_open_form' => ['boolean'],
            'link_edit_form' => ['boolean'],
            'link_edit_submission' => ['boolean'],
            'views_submissions_count' => ['boolean'],
        ];
    }

    public static function getValidationAttributes(): array
    {
        return [
            'data.api_key' => 'Linear API Key',
            'data.team_id' => 'Linear Team',
            'data.project_id' => 'Linear Project',
            'data.state_id' => 'Linear Status',
            'data.title_template' => 'Issue Title',
            'data.description_template' => 'Issue Description Template',
        ];
    }

    protected function getApiKey(): ?string
    {
        return $this->integrationData->api_key ?? null;
    }

    /**
     * Linear API keys go in the raw Authorization header, without a Bearer prefix.
     */
    protected function getRequestHeaders(): array
    {
        return [
            'Authorization' => (string) $this->getApiKey(),
            'Content-Type' => 'application/json',
        ];
    }

    protected function getBaseUrl(): string
    {
        return 'https://api.linear.app/graphql';
    }

    protected function getEndpoint(): string
    {
        return '';
    }

    /**
     * Linear is a GraphQL API, so the request is an envelope of query + variables
     * rather than the payload itself.
     */
    public function handle(): void
    {
        if (! $this->shouldRun()) {
            return;
        }

        Http::timeout(15)
            ->withHeaders($this->getRequestHeaders())
            ->post($this->getBaseUrl(), [
                'query' => <<<'GRAPHQL'
                    mutation IssueCreate($input: IssueCreateInput!) {
                      issueCreate(input: $input) {
                        success
                        issue {
                          id
                          identifier
                          title
                          url
                        }
                      }
                    }
                GRAPHQL,
                'variables' => [
                    'input' => $this->formatPayload(),
                ],
            ])
            ->throw();
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
        $title = Arr::get($settings, 'title_template', '');
        if (empty($title)) {
            $title = $this->form->title.' - Submission';
        } else {
            $title = (new MentionParser($title, $formattedData, $this->getComputedValues()))->parseAsText();
        }

        $input = [
            'teamId' => Arr::get($settings, 'team_id'),
            'title' => $title,
            'description' => $this->buildDescription((array) $settings, $formattedData),
        ];

        if ($projectId = Arr::get($settings, 'project_id')) {
            $input['projectId'] = $projectId;
        }
        if ($stateId = Arr::get($settings, 'state_id')) {
            $input['stateId'] = $stateId;
        }
        if (! is_null(Arr::get($settings, 'priority')) && Arr::get($settings, 'priority') !== '') {
            $input['priority'] = (int) Arr::get($settings, 'priority');
        }
        if ($labelIds = Arr::get($settings, 'label_ids')) {
            $input['labelIds'] = array_values(array_filter(array_map('trim', explode(',', (string) $labelIds))));
        }

        return $input;
    }

    /**
     * Build the issue description in Markdown with optional submission data and links.
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
                $fieldLines[] = "**{$field['name']}:** {$value}";
            }

            if (! empty($fieldLines)) {
                $parts[] = implode("\n\n", $fieldLines);
            }
        }

        if (Arr::get($settings, 'views_submissions_count', true)) {
            $parts[] = "👀 Views: {$this->form->views_count}\n🖊️ Submissions: {$this->form->submissions_count}";
        }

        $links = [];
        if (Arr::get($settings, 'link_open_form', true)) {
            $links[] = "[🔗 Open Form]({$this->form->share_url})";
        }
        if (Arr::get($settings, 'link_edit_form', true)) {
            $editUrl = front_url('forms/'.$this->form->slug.'/show');
            $links[] = "[✏️ Edit Form]({$editUrl})";
        }
        if (Arr::get($settings, 'link_edit_submission', true) && $this->form->editable_submissions && isset($this->submissionData['submission_id'])) {
            $editUrl = SubmissionUrlService::buildEditUrl($this->form, $this->submissionData['submission_id']);
            $links[] = "[✏️ Edit Submission]({$editUrl})";
        }

        if (! empty($links)) {
            $parts[] = implode(' | ', $links);
        }

        return implode("\n\n---\n\n", array_filter($parts));
    }

    protected function shouldRun(): bool
    {
        return parent::shouldRun()
            && $this->form->workspace?->hasFeature('integrations.linear')
            && ! empty($this->getApiKey())
            && ! empty($this->integrationData->team_id);
    }
}
