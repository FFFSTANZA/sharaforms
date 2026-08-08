<?php

use App\Service\AI\Prompts\Form\GenerateFormFieldsPrompt;

uses(\Tests\TestCase::class);

beforeEach(function () {
    config()->set('services.ai.provider', 'openai');
    config()->set('services.ai.providers.openai.api_key', 'test-key');
});

it('prompts for a wrapper object with a properties array', function () {
    expect(GenerateFormFieldsPrompt::PROMPT_TEMPLATE)
        ->toContain('"properties"')
        ->toContain('array of field objects');
});

it('tolerates providers that return a bare array of properties', function () {
    $prompt = new GenerateFormFieldsPrompt('Add an email field', 'Contact form', [], []);

    $result = $prompt->processOutput([
        ['type' => 'email', 'name' => 'Email address', 'help' => 'Your email', 'required' => true],
    ]);

    expect($result)->toBeArray()
        ->toHaveCount(1)
        ->and($result[0]['type'])->toBe('email')
        ->and($result[0]['name'])->toBe('Email address')
        ->and($result[0]['id'])->not->toBeNull();
});

it('accepts the wrapped {properties: [...]} output', function () {
    $prompt = new GenerateFormFieldsPrompt('Add a name field', 'Contact form', [], []);

    $result = $prompt->processOutput([
        'properties' => [
            ['type' => 'text', 'name' => 'Full name'],
        ],
    ]);

    expect($result)->toHaveCount(1)
        ->and($result[0]['name'])->toBe('Full name');
});

it('normalizes model-invented aliases before returning fields', function () {
    $prompt = new GenerateFormFieldsPrompt('Add fields', 'Contact form', [], []);

    $result = $prompt->processOutput([
        ['label' => 'Phone', 'help_text' => 'Digits only', 'type' => 'phone_number', 'required' => 'true'],
    ]);

    expect($result)->toHaveCount(1)
        ->and($result[0]['name'])->toBe('Phone')
        ->and($result[0]['help'])->toBe('Digits only')
        ->and($result[0]['required'])->toBeTrue();
});
