<?php

namespace App\Service\OpenAi;

use App\Service\AI\AiProviderManager;
use App\Service\OpenAi\Utils\JsonFixer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use OpenAI\Client;
use OpenAI\Exceptions\ErrorException;

/**
 * Handles a GPT completion prompt with or without insert tag.
 * Also parses output.
 */
class GptCompleter
{
    protected Client $openAi;

    protected string $provider;

    protected mixed $result;

    protected array $completionInput;

    protected ?string $systemMessage;

    protected bool $expectsJson = false;

    protected int $inputTokens = 0;
    protected int $outputTokens = 0;

    protected bool $useStreaming = false;

    public function __construct(protected string $model, ?string $apiKey = null, protected int $retries = 2)
    {
        $this->provider = $apiKey ? AiProviderManager::PROVIDER_OPENAI : (AiProviderManager::activeProvider() ?? AiProviderManager::PROVIDER_OPENAI);
        $this->model = AiProviderManager::resolveModel($this->model, $this->provider);
        $this->openAi = AiProviderManager::client($apiKey, $this->provider);
    }

    public function setAiModel(string $model)
    {
        $this->model = AiProviderManager::resolveModel($model, $this->provider);

        return $this;
    }

    public function setSystemMessage(string $systemMessage): self
    {
        $this->systemMessage = $systemMessage;

        return $this;
    }

    public function useStreaming(): self
    {
        $this->useStreaming = true;

        return $this;
    }

    public function expectsJson(): self
    {
        $this->expectsJson = true;

        return $this;
    }

    public function doesNotExpectJson(): self
    {
        $this->expectsJson = false;

        return $this;
    }

    public function setJsonSchema(array $schema): self
    {
        $this->completionInput['response_format'] = [
            'type' => 'json_schema',
            'json_schema' => [
                'name' => 'response_schema',
                'strict' => true,
                'schema' => $schema
            ]
        ];

        return $this;
    }

    public function completeChat(array $messages, ?int $maxTokens = 4096, ?float $temperature = 0.81, ?bool $exceptJson = null): self
    {
        // Historical parameter name kept for compatibility; true means JSON is expected.
        if (! is_null($exceptJson)) {
            $this->expectsJson = $exceptJson;
        }
        $this->computeChatCompletion($messages, $maxTokens, $temperature)
            ->queryCompletion();

        return $this;
    }

    public function getBool(): bool
    {
        switch (strtolower($this->result)) {
            case 'true':
                return true;
            case 'false':
                return false;
            default:
                throw new \InvalidArgumentException("Expected a boolean value, got {$this->result}");
        }
    }

    public function getArray(): array
    {
        for ($i = 0; $i < $this->retries; $i++) {
            $payload = Str::of($this->result)->trim();
            if ($payload->contains('```json')) {
                $payload = $payload->after('```json')->before('```');
            } elseif ($payload->contains('```')) {
                $payload = $payload->after('```')->before('```');
            }
            $payload = $payload->toString();
            $exception = null;

            try {
                $newPayload = (new JsonFixer())->fix($payload);

                return json_decode($newPayload, true);
            } catch (\Aws\Exception\InvalidJsonException $e) {
                $exception = $e;
                Log::warning('Invalid JSON, retrying:');
                Log::warning($payload);
                $this->queryCompletion();
            }
        }
        throw $exception;
    }

    public function getHtml(): string
    {
        $payload = Str::of($this->result)->trim();
        if ($payload->contains('```html')) {
            $payload = $payload->after('```html')->before('```');
        } elseif ($payload->contains('```')) {
            $payload = $payload->after('```')->before('```');
        }

        return $payload->toString();
    }

    public function getString(): string
    {
        return trim($this->result);
    }

    public function getInputTokens(): int
    {
        return $this->inputTokens;
    }

    public function getOutputTokens(): int
    {
        return $this->outputTokens;
    }

    protected function computeChatCompletion(array $messages, ?int $maxTokens = 4096, ?float $temperature = 0.81): self
    {
        if (isset($this->systemMessage) && $messages[0]['role'] !== 'system') {
            $messages = array_merge([[
                'role' => 'system',
                'content' => $this->systemMessage,
            ]], $messages);
        }

        $completionInput = [
            'model' => $this->model,
            'messages' => $messages,
        ];

        if (!is_null($maxTokens)) {
            $completionInput[$this->maxTokensParameter()] = $maxTokens;
        }

        if (!is_null($temperature)) {
            $completionInput['temperature'] = $temperature;
        }

        if ($this->expectsJson && !isset($this->completionInput['response_format'])) {
            $completionInput['response_format'] = [
                'type' => 'json_object',
            ];
        } elseif (isset($this->completionInput['response_format'])) {
            $completionInput['response_format'] = $this->normalizedResponseFormat();
        }

        $this->completionInput = $completionInput;

        return $this;
    }

    private function maxTokensParameter(): string
    {
        return $this->provider === AiProviderManager::PROVIDER_OPENAI && str_starts_with($this->model, 'gpt-5')
            ? 'max_completion_tokens'
            : 'max_tokens';
    }

    private function normalizedResponseFormat(): array
    {
        $responseFormat = $this->completionInput['response_format'];

        if ($this->provider !== AiProviderManager::PROVIDER_OPENAI && ($responseFormat['type'] ?? null) === 'json_schema') {
            return [
                'type' => 'json_object',
            ];
        }

        return $responseFormat;
    }

    protected function queryCompletion(): self
    {
        if ($this->useStreaming) {
            return $this->queryStreamedCompletion();
        }

        $attempt = 1;
        $lastError = null;

        while ($attempt <= $this->retries) {
            try {
                $response = $this->createCompletion($this->completionInput);
                $this->captureTokenUsage($response->usage ?? null);
                $this->result = $response->choices[0]->message->content;
                return $this;
            } catch (ErrorException $errorException) {
                $lastError = $errorException;
                Log::warning("AI provider error, retrying: {$errorException->getMessage()}");
                $attempt++;
            }
        }

        throw $lastError ?? new \Exception('Failed to complete AI request after multiple attempts');
    }

    protected function queryStreamedCompletion(): self
    {
        Log::debug('AI provider query: ' . json_encode($this->completionInput));

        $attempt = 1;
        $lastError = null;

        while ($attempt <= $this->retries) {
            try {
                $this->result = '';
                $response = $this->createStreamedCompletion($this->completionInput);
                foreach ($response as $chunk) {
                    $choice = $chunk->choices[0];
                    if (!is_null($choice->delta->content ?? null)) {
                        $this->result .= $choice->delta->content;
                    }

                    $this->captureTokenUsage($chunk->usage ?? null);
                }

                return $this;
            } catch (ErrorException $errorException) {
                $lastError = $errorException;
                Log::warning("AI provider stream error, retrying: {$errorException->getMessage()}");
                $attempt++;
            }
        }

        throw $lastError ?? new \Exception('Failed to complete AI streaming request after multiple attempts');
    }

    protected function createCompletion(array $completionInput): mixed
    {
        return $this->openAi->chat()->create($completionInput);
    }

    protected function createStreamedCompletion(array $completionInput): iterable
    {
        return $this->openAi->chat()->createStreamed($completionInput);
    }

    private function captureTokenUsage(mixed $usage): void
    {
        if (!$usage) {
            return;
        }

        $in = \data_get($usage, 'promptTokens', \data_get($usage, 'prompt_tokens'));
        $out = \data_get($usage, 'completionTokens', \data_get($usage, 'completion_tokens'));

        if ($in !== null) {
            $this->inputTokens = (int) $in;
        }
        if ($out !== null) {
            $this->outputTokens = (int) $out;
        }
    }
}
