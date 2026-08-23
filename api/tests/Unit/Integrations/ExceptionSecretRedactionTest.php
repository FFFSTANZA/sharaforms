<?php

use App\Events\Forms\FormSubmitted;
use App\Integrations\Handlers\LinearIntegration;
use App\Models\Integration\FormIntegration;

uses(\Tests\TestCase::class);

beforeEach(function () {
    $user = $this->actingAsProUser();
    $this->workspace = $this->createUserWorkspace($user);
    $this->form = $this->createForm($user, $this->workspace);
});

function makeHandlerForRedaction($form): LinearIntegration
{
    // redactSecrets() is inherited from the abstract handler; any concrete
    // handler exposes the same behavior.
    $integration = FormIntegration::factory()->for($form)->createQuietly([
        'integration_id' => 'linear',
        'data' => ['api_key' => 'k', 'team_id' => 't'],
    ]);

    return new LinearIntegration(
        new FormSubmitted($integration->form, ['submission_id' => 'sub_1']),
        $integration,
        $integration->toArray()
    );
}

it('redacts sensitive query parameters from exception messages', function () {
    $handler = makeHandlerForRedaction($this->form);

    $message = 'cURL error 6: Could not resolve host for https://api.trello.com/1/cards?key=KEY123&token=TOK456';

    expect($handler->redactSecrets($message))
        ->not->toContain('KEY123')
        ->not->toContain('TOK456')
        ->toContain('REDACTED');
});

it('redacts pipedrive api tokens in urls', function () {
    $handler = makeHandlerForRedaction($this->form);

    $message = 'Connection refused for https://api.pipedrive.com/v1/deals?api_token=SECRET_TOKEN_99';

    $redacted = $handler->redactSecrets($message);

    expect($redacted)->not->toContain('SECRET_TOKEN_99')
        ->and($redacted)->toContain('REDACTED');
});

it('masks telegram bot tokens embedded in paths', function () {
    $handler = makeHandlerForRedaction($this->form);

    $message = 'https://api.telegram.org/bot123456789:AAF3xyzABCDEFabcdef1234567890/sendMessage timed out';

    $redacted = $handler->redactSecrets($message);

    expect($redacted)->not->toContain('AAF3xyzABCDEF')
        ->and($redacted)->toContain('/botREDACTED');
});

it('leaves urls without sensitive params untouched', function () {
    $handler = makeHandlerForRedaction($this->form);

    $url = 'https://hooks.slack.com/services/T000/B000/XXXX?foo=bar';

    expect($handler->redactSecrets($url))->toBe($url);
});
