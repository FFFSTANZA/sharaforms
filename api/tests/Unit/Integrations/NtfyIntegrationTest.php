<?php

use App\Events\Forms\FormSubmitted;
use App\Integrations\Handlers\NtfyIntegration;
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

it('sends notification to ntfy topic', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'ntfy',
        'data' => [
            'ntfy_topic_url' => 'https://ntfy.sh/mytopic',
            'include_submission_data' => true,
        ],
    ]);

    $event = new FormSubmitted($this->form, [
        'submission_id' => 'sub_001',
        $this->form->properties[0]['id'] => 'Test',
    ]);

    $handler = new NtfyIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        return $request->url() === 'https://ntfy.sh/mytopic'
            && $request->method() === 'POST'
            && isset($request->data()['message'])
            && isset($request->data()['topic'])
            && isset($request->data()['title'])
            && isset($request->data()['tags']);
    });
});

it('skips when no topic url is set', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'ntfy',
        'data' => [],
    ]);

    $event = new FormSubmitted($this->form, ['submission_id' => 'sub_001']);
    $handler = new NtfyIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertNothingSent();
});

it('sends priority header when set', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'ntfy',
        'data' => [
            'ntfy_topic_url' => 'https://ntfy.sh/mytopic',
            'priority' => 5,
        ],
    ]);

    $event = new FormSubmitted($this->form, ['submission_id' => 'sub_001']);
    $handler = new NtfyIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        return $request->hasHeader('Priority') && $request->header('Priority')[0] === '5';
    });
});

it('sends tags header when set', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'ntfy',
        'data' => [
            'ntfy_topic_url' => 'https://ntfy.sh/mytopic',
            'tags' => 'rocket,warning',
        ],
    ]);

    $event = new FormSubmitted($this->form, ['submission_id' => 'sub_001']);
    $handler = new NtfyIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        return $request->hasHeader('Tags') && $request->header('Tags')[0] === 'rocket,warning';
    });
});

it('sends click header with custom url', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'ntfy',
        'data' => [
            'ntfy_topic_url' => 'https://ntfy.sh/mytopic',
            'click_url' => 'https://example.com/custom',
        ],
    ]);

    $event = new FormSubmitted($this->form, ['submission_id' => 'sub_001']);
    $handler = new NtfyIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        return $request->hasHeader('Click') && $request->header('Click')[0] === 'https://example.com/custom';
    });
});

it('defaults click url to form share url', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'ntfy',
        'data' => [
            'ntfy_topic_url' => 'https://ntfy.sh/mytopic',
        ],
    ]);

    $event = new FormSubmitted($this->form, ['submission_id' => 'sub_001']);
    $handler = new NtfyIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        return $request->hasHeader('Click') && $request->header('Click')[0] === $this->form->share_url;
    });
});

it('extracts topic name from url', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'ntfy',
        'data' => [
            'ntfy_topic_url' => 'https://ntfy.sh/my-topic-123',
        ],
    ]);

    $event = new FormSubmitted($this->form, ['submission_id' => 'sub_001']);
    $handler = new NtfyIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        return $request->data()['topic'] === 'my-topic-123';
    });
});

it('includes default tags', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'ntfy',
        'data' => [
            'ntfy_topic_url' => 'https://ntfy.sh/mytopic',
        ],
    ]);

    $event = new FormSubmitted($this->form, ['submission_id' => 'sub_001']);
    $handler = new NtfyIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        $tags = $request->data()['tags'];
        return in_array('sharaforms', $tags) && in_array('form', $tags);
    });
});

it('returns correct validation rules', function () {
    $rules = NtfyIntegration::getValidationRules($this->form);
    expect($rules)->toHaveKey('ntfy_topic_url');
    expect($rules)->toHaveKey('priority');
    expect($rules)->toHaveKey('tags');
    expect($rules)->toHaveKey('click_url');
});

it('uses mention parser for message templating', function () {
    $field = $this->form->properties[0];
    $htmlMessage = "New submission: <span mention='true' mention-field-id='{$field['id']}' mention-fallback='unknown'>{$field['name']}</span>";

    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'ntfy',
        'data' => [
            'ntfy_topic_url' => 'https://ntfy.sh/mytopic',
            'message' => $htmlMessage,
            'include_submission_data' => false,
        ],
    ]);

    $event = new FormSubmitted($this->form, [
        'submission_id' => 'sub_001',
        $field['id'] => 'Charlie',
    ]);

    $handler = new NtfyIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        return str_contains($request->data()['message'], 'Charlie');
    });
});
