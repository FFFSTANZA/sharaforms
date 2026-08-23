<?php

use App\Events\Forms\FormSubmitted;
use App\Integrations\Handlers\TrelloIntegration;
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

it('creates trello card with submission data', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'trello',
        'data' => [
            'api_key' => 'trello_key_123',
            'api_token' => 'trello_token_456',
            'list_id' => 'list_789',
        ],
    ]);

    $event = new FormSubmitted($this->form, [
        'submission_id' => 'sub_001',
        $this->form->properties[0]['id'] => 'Test Submission',
    ]);

    $handler = new TrelloIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.trello.com/1/cards?key=trello_key_123&token=trello_token_456'
            && $request->method() === 'POST'
            && isset($request->data()['name'])
            && $request->data()['idList'] === 'list_789';
    });
});

it('skips when no api key is set', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'trello',
        'data' => [],
    ]);

    $event = new FormSubmitted($this->form, ['submission_id' => 'sub_001']);
    $handler = new TrelloIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertNothingSent();
});

it('skips when no list_id is set', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'trello',
        'data' => [
            'api_key' => 'trello_key_123',
            'api_token' => 'trello_token_456',
        ],
    ]);

    $event = new FormSubmitted($this->form, ['submission_id' => 'sub_001']);
    $handler = new TrelloIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertNothingSent();
});

it('uses mention parser for card title', function () {
    $field = $this->form->properties[0];
    $htmlMessage = "New submission: <span mention='true' mention-field-id='{$field['id']}' mention-fallback='someone'>{$field['name']}</span>";

    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'trello',
        'data' => [
            'api_key' => 'trello_key_123',
            'api_token' => 'trello_token_456',
            'list_id' => 'list_789',
            'message' => $htmlMessage,
            'include_submission_data' => false,
            'views_submissions_count' => false,
        ],
    ]);

    $event = new FormSubmitted($this->form, [
        'submission_id' => 'sub_001',
        $field['id'] => 'Alice',
    ]);

    $handler = new TrelloIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        return str_contains($request->data()['name'], 'Alice');
    });
});

it('builds description with submission data', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'trello',
        'data' => [
            'api_key' => 'trello_key_123',
            'api_token' => 'trello_token_456',
            'list_id' => 'list_789',
            'include_submission_data' => true,
        ],
    ]);

    $event = new FormSubmitted($this->form, [
        'submission_id' => 'sub_001',
        $this->form->properties[0]['id'] => 'Jane',
    ]);

    $handler = new TrelloIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        return str_contains($request->data()['desc'], 'Jane');
    });
});

it('includes views and submissions count in description', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'trello',
        'data' => [
            'api_key' => 'trello_key_123',
            'api_token' => 'trello_token_456',
            'list_id' => 'list_789',
            'include_submission_data' => false,
            'views_submissions_count' => true,
        ],
    ]);

    $event = new FormSubmitted($this->form, ['submission_id' => 'sub_001']);
    $handler = new TrelloIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        $desc = $request->data()['desc'];
        return str_contains($desc, 'Views') && str_contains($desc, 'Submissions');
    });
});

it('includes links in description', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'trello',
        'data' => [
            'api_key' => 'trello_key_123',
            'api_token' => 'trello_token_456',
            'list_id' => 'list_789',
            'include_submission_data' => false,
            'views_submissions_count' => false,
            'link_open_form' => true,
        ],
    ]);

    $event = new FormSubmitted($this->form, ['submission_id' => 'sub_001']);
    $handler = new TrelloIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        return str_contains($request->data()['desc'], 'Open Form');
    });
});

it('includes labels when specified', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'trello',
        'data' => [
            'api_key' => 'trello_key_123',
            'api_token' => 'trello_token_456',
            'list_id' => 'list_789',
            'label_ids' => 'label1,label2',
        ],
    ]);

    $event = new FormSubmitted($this->form, ['submission_id' => 'sub_001']);
    $handler = new TrelloIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        return $request->data()['idLabels'] === 'label1,label2';
    });
});

it('includes members when specified', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'trello',
        'data' => [
            'api_key' => 'trello_key_123',
            'api_token' => 'trello_token_456',
            'list_id' => 'list_789',
            'member_ids' => 'member1',
        ],
    ]);

    $event = new FormSubmitted($this->form, ['submission_id' => 'sub_001']);
    $handler = new TrelloIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        return $request->data()['idMembers'] === 'member1';
    });
});

it('uses custom description template with mentions', function () {
    $field = $this->form->properties[0];
    $descTemplate = "Report from <span mention='true' mention-field-id='{$field['id']}' mention-fallback='unknown'>{$field['name']}</span>";

    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'trello',
        'data' => [
            'api_key' => 'trello_key_123',
            'api_token' => 'trello_token_456',
            'list_id' => 'list_789',
            'card_description_template' => $descTemplate,
            'include_submission_data' => false,
            'views_submissions_count' => false,
        ],
    ]);

    $event = new FormSubmitted($this->form, [
        'submission_id' => 'sub_001',
        $field['id'] => 'Bob',
    ]);

    $handler = new TrelloIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        return str_contains($request->data()['desc'], 'Bob');
    });
});

it('returns correct validation rules', function () {
    $rules = TrelloIntegration::getValidationRules($this->form);
    expect($rules)->toHaveKey('api_key');
    expect($rules)->toHaveKey('api_token');
    expect($rules)->toHaveKey('list_id');
    expect($rules)->toHaveKey('message');
    expect($rules)->toHaveKey('card_description_template');
    expect($rules)->toHaveKey('include_submission_data');
    expect($rules)->toHaveKey('views_submissions_count');
    expect($rules)->toHaveKey('label_ids');
    expect($rules)->toHaveKey('member_ids');
});

it('excludes submission data when disabled', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'trello',
        'data' => [
            'api_key' => 'trello_key_123',
            'api_token' => 'trello_token_456',
            'list_id' => 'list_789',
            'include_submission_data' => false,
            'views_submissions_count' => false,
        ],
    ]);

    $event = new FormSubmitted($this->form, [
        'submission_id' => 'sub_001',
        $this->form->properties[0]['id'] => 'Secret Data',
    ]);

    $handler = new TrelloIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        return ! str_contains($request->data()['desc'], 'Secret Data');
    });
});
