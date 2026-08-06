<?php

use App\Service\AI\AiProviderManager;
use App\Service\OpenAi\GptCompleter;

uses(\Tests\TestCase::class);

it('downgrades json schema response formats for non-openai providers', function () {
    config()->set('services.ai.provider', 'gemini');
    config()->set('services.ai.providers.openai.api_key', null);
    config()->set('services.ai.providers.gemini.api_key', 'gemini-test-key');

    $completer = new GptCompleter('gpt-5.4-mini');
    $completer->setJsonSchema([
        'type' => 'object',
        'properties' => [
            'ok' => ['type' => 'boolean'],
        ],
    ]);

    $method = new ReflectionMethod(GptCompleter::class, 'computeChatCompletion');
    $method->setAccessible(true);
    $method->invoke($completer, [['role' => 'user', 'content' => 'Say ok.']], 64, 0.2);

    $property = new ReflectionProperty(GptCompleter::class, 'completionInput');
    $property->setAccessible(true);
    $payload = $property->getValue($completer);

    expect($payload['response_format'])->toBe([
        'type' => 'json_object',
    ]);
});

it('keeps json schema response formats for openai providers', function () {
    config()->set('services.ai.provider', 'openai');
    config()->set('services.ai.providers.openai.api_key', 'openai-test-key');

    $completer = new GptCompleter('gpt-5.4-mini');
    $completer->setJsonSchema([
        'type' => 'object',
        'properties' => [
            'ok' => ['type' => 'boolean'],
        ],
    ]);

    $method = new ReflectionMethod(GptCompleter::class, 'computeChatCompletion');
    $method->setAccessible(true);
    $method->invoke($completer, [['role' => 'user', 'content' => 'Say ok.']], 64, 0.2);

    $property = new ReflectionProperty(GptCompleter::class, 'completionInput');
    $property->setAccessible(true);
    $payload = $property->getValue($completer);

    expect($payload['response_format']['type'])->toBe('json_schema');
});

it('appends streamed content even when the chunk also includes a role and captures usage', function () {
    $completer = new class('gpt-5.4-mini') extends GptCompleter {
        public function __construct(string $model)
        {
            $this->model = $model;
            $this->provider = AiProviderManager::PROVIDER_GEMINI;
            $this->retries = 1;
        }

        protected function createStreamedCompletion(array $completionInput): iterable
        {
            return [
                (object) [
                    'choices' => [
                        (object) [
                            'delta' => (object) [
                                'role' => 'assistant',
                                'content' => 'Hel',
                            ],
                        ],
                    ],
                ],
                (object) [
                    'choices' => [
                        (object) [
                            'delta' => (object) [
                                'content' => 'lo',
                            ],
                        ],
                    ],
                    'usage' => (object) [
                        'promptTokens' => 7,
                        'completionTokens' => 5,
                    ],
                ],
            ];
        }
    };

    $completer->useStreaming()->completeChat([
        ['role' => 'user', 'content' => 'Say hello.'],
    ], 64, 0.2);

    expect($completer->getString())->toBe('Hello')
        ->and($completer->getInputTokens())->toBe(7)
        ->and($completer->getOutputTokens())->toBe(5);
});
