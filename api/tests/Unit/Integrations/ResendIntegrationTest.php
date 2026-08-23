<?php

use App\Events\Forms\FormSubmitted;
use App\Integrations\Handlers\ResendIntegration;
use App\Models\Integration\FormIntegration;
use Illuminate\Support\Facades\Http;

uses(\Tests\TestCase::class);

// Payload-formatting tests use fake HTTP targets; relax the SSRF policy
// so no live DNS lookup is needed. Guard enforcement has dedicated tests.
beforeEach(function () {
    config(['sharaforms.webhooks.allow_private_urls' => true]);
    Http::fake();
    $user = $this->actingAsProUser();
    $this->workspace = $this->createUserWorkspace($user);
    $this->form = $this->createForm($user, $this->workspace);
});

it('sends email via resend api with bearer auth', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'resend',
        'data' => [
            'api_key' => 're_test_key_123',
            'from' => 'forms@example.com',
            'to' => 'one@example.com',
            'subject' => 'New submission',
        ],
    ]);

    $event = new FormSubmitted($this->form, [
        'submission_id' => 'sub_001',
        $this->form->properties[0]['id'] => 'Alice',
    ]);

    $handler = new ResendIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        $data = $request->data();

        return $request->url() === 'https://api.resend.com/emails'
            && $request->method() === 'POST'
            && $request->header('Authorization')[0] === 'Bearer re_test_key_123'
            && $data['from'] === 'forms@example.com'
            && $data['to'] === ['one@example.com']
            && $data['subject'] === 'New submission'
            && str_contains($data['html'], $this->form->properties[0]['name'])
            && str_contains($data['html'], 'Alice');
    });
});

it('parses multiple recipients and named from address', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'resend',
        'data' => [
            'api_key' => 're_test_key_123',
            'from' => 'SharaForms <no-reply@sharaforms.dev>',
            'to' => "a@example.com, b@example.com",
            'cc' => 'c@example.com',
            'bcc' => "d@example.com\ne@example.com",
            'reply_to' => 'support@example.com',
            'subject' => 'New submission',
        ],
    ]);

    $event = new FormSubmitted($this->form, ['submission_id' => 'sub_001']);
    $handler = new ResendIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        $data = $request->data();

        return $data['from'] === 'SharaForms <no-reply@sharaforms.dev>'
            && $data['to'] === ['a@example.com', 'b@example.com']
            && $data['cc'] === ['c@example.com']
            && $data['bcc'] === ['d@example.com', 'e@example.com']
            && $data['reply_to'] === ['support@example.com'];
    });
});

it('enforces the 50 recipient cap through the rule closure', function () {
    $rules = ResendIntegration::getValidationRules($this->form);
    $closure = collect($rules['to'])->first(fn ($rule) => is_object($rule) && is_callable($rule));

    expect($closure)->not->toBeNull();

    request()->merge(['data' => [
        'to' => implode(',', array_map(fn ($i) => "u{$i}@example.com", range(1, 40))),
        'cc' => implode(',', array_map(fn ($i) => "c{$i}@example.com", range(1, 20))),
        'bcc' => '',
    ]]);

    $failed = null;
    $closure('data.to', 'x@example.com', function ($message) use (&$failed) {
        $failed = $message;
    });

    expect($failed)->toContain('50');
});

it('escapes html in field values', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'resend',
        'data' => [
            'api_key' => 're_test_key_123',
            'from' => 'forms@example.com',
            'to' => 'one@example.com',
            'subject' => 'New submission',
        ],
    ]);

    $event = new FormSubmitted($this->form, [
        'submission_id' => 'sub_001',
        $this->form->properties[0]['id'] => '<script>alert(1)</script>',
    ]);

    $handler = new ResendIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        return str_contains($request->data()['html'], '&lt;script&gt;')
            && ! str_contains($request->data()['html'], '<script>');
    });
});

it('skips when no api key is set', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'resend',
        'data' => [
            'from' => 'forms@example.com',
            'to' => 'one@example.com',
            'subject' => 'New submission',
        ],
    ]);

    $event = new FormSubmitted($this->form, ['submission_id' => 'sub_001']);
    $handler = new ResendIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertNothingSent();
});

it('returns correct validation rules', function () {
    $rules = ResendIntegration::getValidationRules($this->form);

    expect($rules)->toHaveKey('api_key')
        ->and($rules)->toHaveKey('from')
        ->and($rules)->toHaveKey('to')
        ->and($rules)->toHaveKey('subject')
        ->and($rules)->toHaveKey('body_template')
        ->and($rules)->toHaveKey('reply_to');
});
