<?php

use App\Events\Forms\FormSubmitted;
use App\Integrations\Handlers\GoogleChatIntegration;
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

it('sends google chat card payload to webhook', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'google_chat',
        'data' => [
            'gchat_webhook_url' => 'https://chat.googleapis.com/v1/spaces/test/messages?key=value',
            'include_submission_data' => true,
        ],
    ]);

    $event = new FormSubmitted($this->form, [
        'submission_id' => 'sub_001',
        $this->form->properties[0]['id'] => 'Test User',
    ]);

    $handler = new GoogleChatIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        return $request->url() === 'https://chat.googleapis.com/v1/spaces/test/messages?key=value'
            && isset($request->data()['cardsV2'])
            && isset($request->data()['text']);
    });
});

it('skips when no webhook url is set', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'google_chat',
        'data' => [],
    ]);

    $event = new FormSubmitted($this->form, ['submission_id' => 'sub_001']);
    $handler = new GoogleChatIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertNothingSent();
});

it('returns correct validation rules', function () {
    $rules = GoogleChatIntegration::getValidationRules($this->form);
    expect($rules)->toHaveKey('gchat_webhook_url');
    expect($rules['gchat_webhook_url'])->toContain('required');
    expect($rules)->toHaveKey('views_submissions_count');
});

it('includes submission data section when enabled', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'google_chat',
        'data' => [
            'gchat_webhook_url' => 'https://chat.googleapis.com/v1/spaces/test/messages?key=value',
            'include_submission_data' => true,
        ],
    ]);

    $event = new FormSubmitted($this->form, [
        'submission_id' => 'sub_001',
        $this->form->properties[0]['id'] => 'Jane Smith',
    ]);

    $handler = new GoogleChatIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        $card = $request->data()['cardsV2'][0]['card'];
        $hasSection = false;
        foreach ($card['sections'] as $section) {
            if (($section['header'] ?? '') === 'Submission Data') {
                $hasSection = true;
                break;
            }
        }
        return $hasSection;
    });
});

it('includes views and submissions count section', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'google_chat',
        'data' => [
            'gchat_webhook_url' => 'https://chat.googleapis.com/v1/spaces/test/messages?key=value',
            'include_submission_data' => false,
            'views_submissions_count' => true,
        ],
    ]);

    $event = new FormSubmitted($this->form, ['submission_id' => 'sub_001']);
    $handler = new GoogleChatIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        $card = $request->data()['cardsV2'][0]['card'];
        $hasAnalytics = false;
        foreach ($card['sections'] as $section) {
            if (($section['header'] ?? '') === 'Form Analytics') {
                $hasAnalytics = true;
                break;
            }
        }
        return $hasAnalytics;
    });
});

it('includes action buttons section', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'google_chat',
        'data' => [
            'gchat_webhook_url' => 'https://chat.googleapis.com/v1/spaces/test/messages?key=value',
            'link_open_form' => true,
            'link_edit_form' => false,
        ],
    ]);

    $event = new FormSubmitted($this->form, ['submission_id' => 'sub_001']);
    $handler = new GoogleChatIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        $card = $request->data()['cardsV2'][0]['card'];
        $hasButtonList = false;
        foreach ($card['sections'] as $section) {
            foreach ($section['widgets'] ?? [] as $widget) {
                if (isset($widget['buttonList'])) {
                    $hasButtonList = true;
                    break 2;
                }
            }
        }
        return $hasButtonList;
    });
});

it('includes form title and subtitle in card header', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'google_chat',
        'data' => [
            'gchat_webhook_url' => 'https://chat.googleapis.com/v1/spaces/test/messages?key=value',
        ],
    ]);

    $event = new FormSubmitted($this->form, ['submission_id' => 'sub_001']);
    $handler = new GoogleChatIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        $header = $request->data()['cardsV2'][0]['card']['header'];
        return isset($header['title']) && isset($header['subtitle']);
    });
});

it('uses mention parser for message templating', function () {
    $field = $this->form->properties[0];
    $htmlMessage = "Hello <span mention='true' mention-field-id='{$field['id']}' mention-fallback='someone'>{$field['name']}</span>";

    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'google_chat',
        'data' => [
            'gchat_webhook_url' => 'https://chat.googleapis.com/v1/spaces/test/messages?key=value',
            'message' => $htmlMessage,
            'include_submission_data' => false,
            'views_submissions_count' => false,
        ],
    ]);

    $event = new FormSubmitted($this->form, [
        'submission_id' => 'sub_001',
        $field['id'] => 'Bob',
    ]);

    $handler = new GoogleChatIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        return str_contains($request->data()['text'], 'Bob');
    });
});

it('escapes html in submission data', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'google_chat',
        'data' => [
            'gchat_webhook_url' => 'https://chat.googleapis.com/v1/spaces/test/messages?key=value',
            'include_submission_data' => true,
        ],
    ]);

    $event = new FormSubmitted($this->form, [
        'submission_id' => 'sub_001',
        $this->form->properties[0]['id'] => '<img src=x onerror=alert(1)>',
    ]);

    $handler = new GoogleChatIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        $card = json_encode($request->data());
        return ! str_contains($card, '<img') && str_contains($card, '&lt;img');
    });
});
