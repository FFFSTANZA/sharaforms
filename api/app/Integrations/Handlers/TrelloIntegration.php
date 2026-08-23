<?php

namespace App\Integrations\Handlers;

use App\Models\Forms\Form;
use App\Open\MentionParser;
use App\Service\Forms\FormSubmissionFormatter;
use App\Service\Forms\SubmissionUrlService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class TrelloIntegration extends ApiKeyIntegrationHandler
{
    public static function getValidationRules(?Form $form): array
    {
        return [
            'api_key' => ['required', 'string'],
            'api_token' => ['required', 'string'],
            'board_id' => ['nullable', 'string'],
            'list_id' => ['required', 'string'],
            'message' => ['nullable', 'string'],
            'card_description_template' => ['nullable', 'string'],
            'include_submission_data' => ['boolean'],
            'include_hidden_fields_submission_data' => ['nullable', 'boolean'],
            'link_open_form' => ['boolean'],
            'link_edit_form' => ['boolean'],
            'link_edit_submission' => ['boolean'],
            'views_submissions_count' => ['boolean'],
            'label_ids' => ['nullable', 'string'],
            'member_ids' => ['nullable', 'string'],
        ];
    }

    public static function getValidationAttributes(): array
    {
        return [
            'data.api_key' => 'Trello API Key',
            'data.api_token' => 'Trello API Token',
            'data.board_id' => 'Trello Board',
            'data.list_id' => 'Trello List',
        ];
    }

    protected function getApiKey(): ?string
    {
        return $this->integrationData->api_key ?? null;
    }

    /**
     * Trello requires key+token as query parameters, not headers.
     */
    protected function getRequestHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
        ];
    }

    protected function getBaseUrl(): string
    {
        return 'https://api.trello.com/1';
    }

    protected function getEndpoint(): string
    {
        $settings = (array) ($this->integrationData ?? []);
        $apiKey = $settings['api_key'] ?? '';
        $apiToken = $settings['api_token'] ?? '';

        // Append key+token as query params
        return 'cards?key='.$apiKey.'&token='.$apiToken;
    }

    protected function formatPayload(): array
    {
        $settings = (array) ($this->integrationData ?? []);
        $listId = $settings['list_id'] ?? '';

        // Build submission data for mention parsing
        $formattedData = $this->escapeFormattedData(
            (new FormSubmissionFormatter($this->form, $this->submissionData))
                ->outputStringsOnly()
                ->showHiddenFields()
                ->getFieldsWithValue()
        );

        // Card name: use mention template or default
        $cardName = Arr::get($settings, 'message', '');
        if (empty($cardName)) {
            $cardName = $this->form->title.' - Submission';
        } else {
            $cardName = (new MentionParser($cardName, $formattedData, $this->getComputedValues()))->parseAsText();
        }

        // Card description
        $desc = $this->buildDescription($settings, $formattedData);

        $payload = [
            'name' => $cardName,
            'desc' => $desc,
            'idList' => $listId,
        ];

        // Labels
        $labelIds = Arr::get($settings, 'label_ids');
        if ($labelIds) {
            $payload['idLabels'] = $labelIds;
        }

        // Members
        $memberIds = Arr::get($settings, 'member_ids');
        if ($memberIds) {
            $payload['idMembers'] = $memberIds;
        }

        return $payload;
    }

    /**
     * Build card description with optional submission data and links.
     */
    private function buildDescription(array $settings, array $formattedData): string
    {
        $parts = [];

        // Custom description template
        $template = Arr::get($settings, 'card_description_template');
        if ($template) {
            $parts[] = (new MentionParser($template, $formattedData, $this->getComputedValues()))->parseAsText();
        }

        // Submission data
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

        // Views & submissions count
        if (Arr::get($settings, 'views_submissions_count', true)) {
            $parts[] = "👀 Views: {$this->form->views_count}\n🖊️ Submissions: {$this->form->submissions_count}";
        }

        // Links
        $links = [];
        if (Arr::get($settings, 'link_open_form', true)) {
            $links[] = "[🔗 Open Form]({$this->form->share_url})";
        }
        if (Arr::get($settings, 'link_edit_form', true)) {
            $editUrl = front_url('forms/'.$this->form->slug.'/show');
            $links[] = "[✏️ Edit Form]({$editUrl})";
        }
        if (Arr::get($settings, 'link_edit_submission', true) && $this->form->editable_submissions) {
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
            && $this->form->workspace?->hasFeature('integrations.trello')
            && ! empty($this->integrationData->list_id);
    }
}
