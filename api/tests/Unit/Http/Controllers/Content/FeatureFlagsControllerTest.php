<?php

use App\Http\Controllers\Content\FeatureFlagsController;

uses(\Tests\TestCase::class);

it('enables ai feature flags when any supported ai provider key is configured', function () {
    config()->set('services.ai.provider', 'auto');
    config()->set('services.ai.providers.openai.api_key', null);
    config()->set('services.ai.providers.gemini.api_key', 'gemini-test-key');
    config()->set('services.ai.providers.groq.api_key', null);

    $response = app(FeatureFlagsController::class)->index();
    $payload = $response->getData(true);

    expect($payload['ai_features'])->toBeTrue();
});

it('disables ai feature flags when no supported ai provider key is configured', function () {
    config()->set('services.ai.provider', 'auto');
    config()->set('services.ai.providers.openai.api_key', null);
    config()->set('services.ai.providers.gemini.api_key', null);
    config()->set('services.ai.providers.groq.api_key', null);

    $response = app(FeatureFlagsController::class)->index();
    $payload = $response->getData(true);

    expect($payload['ai_features'])->toBeFalse();
});
