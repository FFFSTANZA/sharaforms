<?php

use App\Service\AI\AiProviderManager;

uses(\Tests\TestCase::class);

it('honors an explicit provider override when its key is configured', function () {
    config()->set('services.ai.provider', 'groq');
    config()->set('services.ai.providers.openai.api_key', 'openai-test-key');
    config()->set('services.ai.providers.gemini.api_key', 'gemini-test-key');
    config()->set('services.ai.providers.groq.api_key', 'groq-test-key');

    expect(AiProviderManager::activeProvider())->toBe(AiProviderManager::PROVIDER_GROQ);
});

it('falls back to the first configured provider when the preferred provider has no key', function () {
    config()->set('services.ai.provider', 'groq');
    config()->set('services.ai.providers.openai.api_key', null);
    config()->set('services.ai.providers.gemini.api_key', 'gemini-test-key');
    config()->set('services.ai.providers.groq.api_key', null);

    expect(AiProviderManager::activeProvider())->toBe(AiProviderManager::PROVIDER_GEMINI);
});

it('reports when no provider is available', function () {
    config()->set('services.ai.provider', 'auto');
    config()->set('services.ai.providers.openai.api_key', null);
    config()->set('services.ai.providers.gemini.api_key', null);
    config()->set('services.ai.providers.groq.api_key', null);

    expect(AiProviderManager::activeProvider())->toBeNull()
        ->and(AiProviderManager::hasAvailableProvider())->toBeFalse();
});

it('throws when building a client without a configured key', function () {
    config()->set('services.ai.providers.openai.api_key', null);

    expect(fn () => AiProviderManager::client(null, AiProviderManager::PROVIDER_OPENAI))
        ->toThrow(InvalidArgumentException::class, 'No API key configured for AI provider [openai].');
});
