<?php

namespace App\Integrations\Handlers;

use App\Models\Integration\FormIntegration;
use App\Events\Forms\FormSubmitted;
use App\Models\Forms\Form;
use App\Models\Integration\FormIntegrationsEvent;
use App\Service\Forms\FormSubmissionFormatter;
use App\Service\Forms\FormLogicConditionChecker;
use App\Service\Forms\SubmissionUrlService;
use App\Service\Formulas\ComputedVariableEvaluator;
use App\Service\Security\PublicWebhookUrl;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class AbstractIntegrationHandler
{
    protected $form = null;
    protected $submissionData = null;
    protected $integrationData = null;
    protected $provider = null;
    protected ?array $computedValues = null;

    public function __construct(
        protected FormSubmitted $event,
        protected FormIntegration $formIntegration,
        protected array $integration
    ) {
        $this->form = $event->form;
        $this->submissionData = $event->data;
        $this->integrationData = $formIntegration->data;
        $this->provider = $formIntegration->provider;
    }

    /**
     * Get computed variable values for this submission
     */
    protected function getComputedValues(): array
    {
        if ($this->computedValues === null) {
            $this->computedValues = ComputedVariableEvaluator::evaluateForSubmission(
                $this->form,
                $this->submissionData
            );
        }
        return $this->computedValues;
    }

    protected function getProviderName(): string
    {
        return $this->integration['name'] ?? '';
    }

    protected function logicConditionsMet(): bool
    {
        if (!$this->formIntegration->logic || empty((array) $this->formIntegration->logic)) {
            return true;
        }
        return FormLogicConditionChecker::conditionsMetWithForm(
            json_decode(json_encode($this->formIntegration->logic), true),
            $this->submissionData,
            $this->form
        );
    }

    protected function shouldRun(): bool
    {
        return $this->logicConditionsMet();
    }

    protected function getWebhookUrl(): ?string
    {
        return '';
    }

    /**
     * Default webhook payload. Can be changed in child classes.
     */
    protected function getWebhookData(): array
    {
        return self::formatWebhookData($this->form, $this->submissionData);
    }

    final public function run(): void
    {
        try {
            $this->handle();
            $this->formIntegration->events()->create([
                'status' => FormIntegrationsEvent::STATUS_SUCCESS,
            ]);
        } catch (\Throwable $e) {
            // Catch Throwable (not just Exception) so a single broken handler
            // can never abort the remaining integrations for this submission.
            $this->formIntegration->events()->create([
                'status' => FormIntegrationsEvent::STATUS_ERROR,
                'data' => $this->extractEventDataFromException($e),
            ]);
            Log::error('Integration failed', array_merge([
                'form_id' => $this->form->id,
                'integration_id' => $this->formIntegration->id,
            ], $this->extractEventDataFromException($e)));
        }
    }

    public function created(): void
    {
        //
    }

    /**
     * Default handle. Can be changed in child classes.
     */
    public function handle(): void
    {
        if (!$this->shouldRun()) {
            return;
        }

        $url = $this->getWebhookUrl();
        PublicWebhookUrl::assertSafe($url);

        Http::throw()
            ->withOptions(PublicWebhookUrl::requestOptions($url))
            ->post($url, $this->getWebhookData());
    }

    abstract public static function getValidationRules(?Form $form): array;

    public static function isOAuthRequired(): bool
    {
        return false;
    }

    public static function getValidationAttributes(): array
    {
        return [];
    }

    public static function formatWebhookData(Form $form, array $submissionData): array
    {
        $formatter = (new FormSubmissionFormatter($form, $submissionData))
            ->useSignedUrlForFiles()
            ->showHiddenFields();

        // Old format - kept for retro-compatibility
        $oldFormatData = [];
        $formattedData = [];
        $fieldsWithValue = $formatter->getFieldsWithValue();

        foreach ($fieldsWithValue as $field) {
            $oldFormatData[$field['name']] = $field['value'];
            // New format using ID
            $formattedData[$field['id']] = [
                'value' => $field['value'],
                'name' => $field['name'],
                'type' => $field['type']
            ];
        }

        $data = [
            'form_id' => $form->id,
            'form_title' => $form->title,
            'form_slug' => $form->slug,
            'submission' => $oldFormatData,
            'data' => $formattedData,
            'message' => 'Please do not use the `submission` field. It is deprecated and will be removed in the future.'
        ];
        if (isset($submissionData['submission_id'])) {
            $data['submission_id'] = $submissionData['submission_id'];
        }
        if ($form->workspace?->hasFeature('editable_submissions') && $form->editable_submissions && isset($submissionData['submission_id'])) {
            $data['edit_link'] = SubmissionUrlService::buildEditUrl($form, $submissionData['submission_id']);
        }

        return $data;
    }

    /**
     * Query parameter keys whose values must never be persisted or logged.
     * Some APIs authenticate via query params (e.g. Trello key/token,
     * Pipedrive api_token) and connection-level exceptions embed full URLs.
     */
    public const SENSITIVE_QUERY_KEYS = [
        'key', 'token', 'api_key', 'api_token', 'apikey', 'apikeytoken',
        'secret', 'signature', 'password', 'access_token', 'client_secret',
    ];

    public function extractEventDataFromException(\Throwable $e): array
    {
        if ($e instanceof RequestException) {
            return [
                'message' => $this->redactSecrets($e->getMessage()),
                'response' => $e->response->json(),
                'status' => $e->response->status(),
            ];
        }
        return [
            'message' => $this->redactSecrets($e->getMessage())
        ];
    }

    /**
     * Redact credentials that may appear inside exception message strings:
     * sensitive query parameters and bot tokens embedded in URL paths.
     */
    public function redactSecrets(string $text): string
    {
        // Bot tokens in paths, e.g. https://api.telegram.org/bot<token>/sendMessage
        $text = preg_replace('/(\/bot)[A-Za-z0-9:_\-]{20,}/', '$1REDACTED', $text);

        // Sensitive query parameters on any URL mentioned in the message
        return (string) preg_replace_callback('/https?:\/\/[^\s"\'<>]+/', function (array $match) {
            $parts = parse_url($match[0]);
            if (empty($parts['query'])) {
                return $match[0];
            }

            parse_str($parts['query'], $query);
            $redacted = false;
            foreach ($query as $key => $value) {
                if (in_array(strtolower((string) $key), self::SENSITIVE_QUERY_KEYS, true)) {
                    $query[$key] = 'REDACTED';
                    $redacted = true;
                }
            }

            if (! $redacted) {
                return $match[0];
            }

            $rebuilt = ($parts['scheme'] ?? 'https').'://'.($parts['host'] ?? '');
            if (! empty($parts['path'])) {
                $rebuilt .= $parts['path'];
            }
            $rebuilt .= '?'.http_build_query($query);
            if (! empty($parts['fragment'])) {
                $rebuilt .= '#'.$parts['fragment'];
            }

            return $rebuilt;
        }, $text);
    }

    /**
     * Used in FormIntegrationRequest to format integration
     */
    public static function formatData(array $data): array
    {
        return $data;
    }

    /**
     * Replace {{FieldName}} placeholders in a template string with actual submission values.
     *
     * Supports two data formats:
     * - Array of field objects with 'name' and 'value' keys (from FormSubmissionFormatter::getFieldsWithValue())
     * - Associative array of field_id => ['name' => ..., 'value' => ...] (from getFormattedSubmissionData())
     */
    protected function replaceVariables(string $template, array $data): string
    {
        $replacements = [];

        foreach ($data as $key => $field) {
            if (is_array($field) && isset($field['name'])) {
                $name = $field['name'];
                $value = is_array($field['value'] ?? null)
                    ? implode(', ', $field['value'])
                    : ($field['value'] ?? '');
            } elseif (is_string($key)) {
                // Associative array: key is field_id, value is ['name' => ..., 'value' => ...]
                $name = $field['name'] ?? $key;
                $value = is_array($field['value'] ?? null)
                    ? implode(', ', $field['value'])
                    : ($field['value'] ?? '');
            } else {
                continue;
            }

            $replacements['{{'.$name.'}}'] = (string) $value;
        }

        return strtr($template, $replacements);
    }
}
