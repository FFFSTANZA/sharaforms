<?php

use App\Events\Forms\FormSubmitted;
use App\Models\Integration\FormIntegration;
use App\Models\OAuthProvider;

uses(\Tests\TestCase::class);

beforeEach(function () {
    $user = $this->actingAsProUser();
    $this->workspace = $this->createUserWorkspace($user);
    $this->form = $this->createForm($user, $this->workspace);
});

function makeTelegramIntegration($form, $provider): FormIntegration
{
    return FormIntegration::factory()->for($form)->createQuietly([
        'integration_id' => 'telegram',
        'oauth_id' => $provider->id,
        'data' => [],
    ]);
}

it('throws a configuration error when no chat is connected', function () {
    config(['services.telegram.bot_token' => '123456:ABC-DEF_test_token']);

    $user = $this->form->creator;
    $provider = OAuthProvider::factory()->for($user)->create([
        'provider' => 'telegram',
        'provider_user_id' => '',
    ]);

    $integration = makeTelegramIntegration($this->form, $provider);

    $event = new FormSubmitted($this->form, ['submission_id' => 'sub_001']);
    $handler = \App\Listeners\Forms\NotifyFormSubmission::getIntegrationHandler($event, $integration);

    expect(fn () => $handler->handle())
        ->toThrow(RuntimeException::class, 'no connected Telegram account');
});

it('records an error event when delivery fails', function () {
    config(['services.telegram.bot_token' => '123456:ABC-DEF_test_token']);

    $user = $this->form->creator;
    $provider = OAuthProvider::factory()->for($user)->create([
        'provider' => 'telegram',
        'provider_user_id' => '',
    ]);

    $integration = makeTelegramIntegration($this->form, $provider);
    $event = new FormSubmitted($this->form, ['submission_id' => 'sub_001']);
    $handler = \App\Listeners\Forms\NotifyFormSubmission::getIntegrationHandler($event, $integration);

    // handle() throws; run() converts that into a persisted error event.
    $handler->run();

    expect($integration->events()->where('status', 'error')->exists())->toBeTrue()
        ->and($integration->events()->where('status', 'success')->exists())->toBeFalse();
});
