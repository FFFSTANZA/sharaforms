<?php

namespace App\Service\AI;

use Illuminate\Support\Facades\Config;

class AiProviderManager
{
    public const PROVIDER_OPENAI = 'openai';
    public const PROVIDER_GEMINI = 'gemini';
    public const PROVIDER_GROQ = 'groq';

    private const PROVIDERS = [
        self::PROVIDER_OPENAI,
        self::PROVIDER_GEMINI,
        self::PROVIDER_GROQ,
    ];

    public static function hasAvailableProvider(): bool
    {
        return self::activeProvider() !== null;
    }

    public static function activeProvider(): ?string
    {
        $preferredProvider = Config::get('services.ai.provider', 'auto');

        if ($preferredProvider !== 'auto' && self::providerHasApiKey($preferredProvider)) {
            return $preferredProvider;
        }

        foreach (self::PROVIDERS as $provider) {
            if (self::providerHasApiKey($provider)) {
                return $provider;
            }
        }

        return null;
    }

    public static function client(?string $apiKey = null, ?string $provider = null): mixed
    {
        $provider = $provider ?? ($apiKey ? self::PROVIDER_OPENAI : self::activeProvider()) ?? self::PROVIDER_OPENAI;
        $providerConfig = self::providerConfig($provider);
        $resolvedApiKey = $apiKey ?? ($providerConfig['api_key'] ?? null);

        if (!is_string($resolvedApiKey) || trim($resolvedApiKey) === '') {
            throw new \InvalidArgumentException("No API key configured for AI provider [{$provider}].");
        }

        $factory = \OpenAI::factory()->withApiKey($resolvedApiKey);

        if (!empty($providerConfig['base_uri'])) {
            $factory = $factory->withBaseUri($providerConfig['base_uri']);
        }

        $factory = $factory->withHttpClient(new \GuzzleHttp\Client([
            'timeout' => (float) Config::get('services.ai.timeout', 10),
            'connect_timeout' => (float) Config::get('services.ai.connect_timeout', 5),
        ]));

        return $factory->make();
    }

    public static function resolveModel(string $model, ?string $provider = null): string
    {
        $provider = $provider ?? self::activeProvider() ?? self::PROVIDER_OPENAI;
        $models = self::providerConfig($provider)['models'] ?? [];

        return match ($model) {
            'gpt-5.4-mini' => $models['mini'] ?? $model,
            'gpt-5.4-nano' => $models['nano'] ?? ($models['mini'] ?? $model),
            default => $model,
        };
    }

    private static function providerHasApiKey(string $provider): bool
    {
        $apiKey = self::providerConfig($provider)['api_key'] ?? null;

        return is_string($apiKey) && trim($apiKey) !== '';
    }

    private static function providerConfig(string $provider): array
    {
        return Config::get("services.ai.providers.{$provider}", []);
    }
}
