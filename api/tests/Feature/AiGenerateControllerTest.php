<?php

use App\Jobs\Form\GenerateAiForm;
use App\Jobs\Form\GenerateAiFormFields;
use App\Models\Forms\AI\AiFormCompletion;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    config()->set('services.ai.provider', 'openai');
    config()->set('services.ai.providers.openai.api_key', 'test-key');
    config()->set('services.ai.providers.gemini.api_key', null);
    config()->set('services.ai.providers.groq.api_key', null);
});

it('creates a form completion bound to the authenticated user and queues the job', function () {
    Queue::fake();

    $user = $this->actingAsUser();

    $this->postJson(aiGenerateRoute(), [
        'form_prompt' => 'A job application form',
        'generation_params' => ['presentation_style' => 'classic'],
    ])->assertOk();

    $completion = AiFormCompletion::query()->firstOrFail();
    expect($completion->user_id)->toBe($user->id)
        ->and($completion->type)->toBe(AiFormCompletion::TYPE_FORM)
        ->and($completion->status)->toBe(AiFormCompletion::STATUS_PENDING);

    Queue::assertPushed(GenerateAiForm::class);
});

it('creates a fields completion bound to the current user', function () {
    Queue::fake();

    $user = $this->actingAsUser();

    $this->postJson(aiGenerateFieldsRoute(), [
        'fields_prompt' => 'Add a phone number question',
        'current_form_structure' => [
            'title' => 'Contact form',
            'properties' => [
                ['name' => 'Email', 'type' => 'email'],
            ],
        ],
        'generation_params' => ['presentation_style' => 'classic'],
    ])->assertOk();

    $completion = AiFormCompletion::query()->firstOrFail();
    expect($completion->user_id)->toBe($user->id)
        ->and($completion->type)->toBe(AiFormCompletion::TYPE_FIELDS);

    Queue::assertPushed(GenerateAiFormFields::class);
});

it('rejects oversized current_form_structure contexts', function () {
    $this->actingAsUser();

    $properties = [];
    for ($i = 0; $i < 501; $i++) {
        $properties[] = ['name' => str_repeat('a', 30), 'type' => 'text'];
    }

    $this->postJson(aiGenerateFieldsRoute(), [
        'fields_prompt' => 'Add fields',
        'current_form_structure' => ['title' => 'T', 'properties' => $properties],
    ])->assertStatus(422);
});

it('rejects oversized top-level current_form_structure payloads', function () {
    $this->actingAsUser();

    $this->postJson(aiGenerateFieldsRoute(), [
        'fields_prompt' => 'Add fields',
        'current_form_structure' => array_fill(0, 11, ['junk' => 'x']),
    ])->assertStatus(422);
});

it('returns 503 when no AI provider is configured', function () {
    $this->actingAsUser();

    config()->set('services.ai.providers.openai.api_key', null);
    config()->set('services.ai.providers.gemini.api_key', null);
    config()->set('services.ai.providers.groq.api_key', null);

    $this->postJson(aiGenerateRoute(), ['form_prompt' => 'A form'])
        ->assertStatus(503);
});

it('lets the completion owner fetch their result', function () {
    $user = $this->actingAsUser();
    $completion = AiFormCompletion::withoutEvents(fn () => AiFormCompletion::create([
        'user_id' => $user->id,
        'form_prompt' => 'A form',
        'status' => AiFormCompletion::STATUS_COMPLETED,
        'result' => json_encode(['title' => 'Done']),
        'ip' => '1.2.3.4',
    ]));

    $this->getJson(aiShowRoute($completion))
        ->assertOk()
        ->assertJsonPath('ai_form_completion.status', AiFormCompletion::STATUS_COMPLETED)
        ->assertJsonPath('ai_form_completion.result', '{"title":"Done"}');
});

it('denies access to completions owned by another user', function () {
    $owner = $this->actingAsUser();
    $completion = AiFormCompletion::withoutEvents(fn () => AiFormCompletion::create([
        'user_id' => $owner->id,
        'form_prompt' => 'A form',
        'status' => AiFormCompletion::STATUS_COMPLETED,
        'result' => json_encode(['secret' => 'data']),
        'ip' => '1.2.3.4',
    ]));

    $other = $this->createUser();
    $this->actingAs($other, 'api');

    $this->getJson(aiShowRoute($completion))->assertStatus(403);
});

it('falls back to the IP check for legacy completions without a user', function () {
    $this->actingAsUser();

    $completion = AiFormCompletion::withoutEvents(fn () => AiFormCompletion::create([
        'user_id' => null,
        'form_prompt' => 'A form',
        'status' => AiFormCompletion::STATUS_PENDING,
        'ip' => '203.0.113.10',
    ]));

    $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
        ->getJson(aiShowRoute($completion))
        ->assertOk();

    $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.99'])
        ->getJson(aiShowRoute($completion))
        ->assertStatus(403);
});

function aiGenerateRoute(): string
{
    return route('forms.ai.generate');
}

function aiGenerateFieldsRoute(): string
{
    return route('forms.ai.generate-fields');
}

function aiShowRoute(AiFormCompletion $completion): string
{
    return route('forms.ai.show', $completion);
}