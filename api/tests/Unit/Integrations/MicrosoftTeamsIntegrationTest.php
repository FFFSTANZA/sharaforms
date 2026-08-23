<?php

use App\Events\Forms\FormSubmitted;
use App\Integrations\Handlers\MicrosoftTeamsIntegration;
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

it('sends adaptive card payload to teams webhook', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'microsoft_teams',
        'data' => [
            'teams_webhook_url' => 'https://test.webhook.office.com/webhookb2/test123',
            'include_submission_data' => true,
        ],
    ]);

    $submissionData = [
        'submission_id' => 'sub_001',
        $this->form->properties[0]['id'] => 'John Doe',
    ];

    $event = new FormSubmitted($this->form, $submissionData);
    $handler = new MicrosoftTeamsIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        return $request->url() === 'https://test.webhook.office.com/webhookb2/test123'
            && $request->method() === 'POST'
            && isset($request->data()['type'])
            && $request->data()['type'] === 'message'
            && isset($request->data()['attachments'][0]['content']['body'])
            && isset($request->data()['themeColor']);
    });
});

it('skips when no webhook url is set', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'microsoft_teams',
        'data' => [],
    ]);

    $event = new FormSubmitted($this->form, ['submission_id' => 'sub_001']);
    $handler = new MicrosoftTeamsIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertNothingSent();
});

it('returns correct validation rules', function () {
    $rules = MicrosoftTeamsIntegration::getValidationRules($this->form);
    expect($rules)->toHaveKey('teams_webhook_url');
    expect($rules['teams_webhook_url'])->toContain('required');
    expect($rules['teams_webhook_url'])->toContain('url');
    expect($rules)->toHaveKey('views_submissions_count');
});

it('includes submission data in payload when enabled', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'microsoft_teams',
        'data' => [
            'teams_webhook_url' => 'https://test.webhook.office.com/webhookb2/test123',
            'include_submission_data' => true,
            'include_hidden_fields_submission_data' => false,
        ],
    ]);

    $event = new FormSubmitted($this->form, [
        'submission_id' => 'sub_001',
        $this->form->properties[0]['id'] => 'Jane Smith',
    ]);

    $handler = new MicrosoftTeamsIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        $body = $request->data()['attachments'][0]['content']['body'];
        $hasFactSet = false;
        foreach ($body as $element) {
            if ($element['type'] === 'FactSet') {
                $hasFactSet = true;
                break;
            }
        }
        return $hasFactSet;
    });
});

it('excludes submission data when disabled', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'microsoft_teams',
        'data' => [
            'teams_webhook_url' => 'https://test.webhook.office.com/webhookb2/test123',
            'include_submission_data' => false,
            'views_submissions_count' => false,
        ],
    ]);

    $event = new FormSubmitted($this->form, [
        'submission_id' => 'sub_001',
        $this->form->properties[0]['id'] => 'Jane Smith',
    ]);

    $handler = new MicrosoftTeamsIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        $body = $request->data()['attachments'][0]['content']['body'];
        foreach ($body as $element) {
            if ($element['type'] === 'FactSet') {
                return false;
            }
        }
        return true;
    });
});

it('includes action buttons', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'microsoft_teams',
        'data' => [
            'teams_webhook_url' => 'https://test.webhook.office.com/webhookb2/test123',
            'link_open_form' => true,
            'link_edit_form' => false,
        ],
    ]);

    $event = new FormSubmitted($this->form, ['submission_id' => 'sub_001']);
    $handler = new MicrosoftTeamsIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        $body = $request->data()['attachments'][0]['content']['body'];
        foreach ($body as $element) {
            if ($element['type'] === 'ActionSet') {
                return count($element['actions']) >= 1;
            }
        }
        return false;
    });
});

it('includes views and submissions count when enabled', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'microsoft_teams',
        'data' => [
            'teams_webhook_url' => 'https://test.webhook.office.com/webhookb2/test123',
            'include_submission_data' => false,
            'views_submissions_count' => true,
        ],
    ]);

    $event = new FormSubmitted($this->form, ['submission_id' => 'sub_001']);
    $handler = new MicrosoftTeamsIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        $body = $request->data()['attachments'][0]['content']['body'];
        foreach ($body as $element) {
            if ($element['type'] === 'FactSet') {
                $names = array_column($element['facts'], 'name');
                return in_array('👀 Views', $names) && in_array('🖊️ Submissions', $names);
            }
        }
        return false;
    });
});

it('uses form color as theme color', function () {
    $this->form->update(['color' => '#ff5733']);

    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'microsoft_teams',
        'data' => [
            'teams_webhook_url' => 'https://test.webhook.office.com/webhookb2/test123',
        ],
    ]);

    $event = new FormSubmitted($this->form, ['submission_id' => 'sub_001']);
    $handler = new MicrosoftTeamsIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        return $request->data()['themeColor'] === 'ff5733';
    });
});

it('uses mention parser for message templating', function () {
    $field = $this->form->properties[0];
    $htmlMessage = "New submission from <span mention='true' mention-field-id='{$field['id']}' mention-fallback='someone'>{$field['name']}</span>";

    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'microsoft_teams',
        'data' => [
            'teams_webhook_url' => 'https://test.webhook.office.com/webhookb2/test123',
            'message' => $htmlMessage,
            'include_submission_data' => false,
            'views_submissions_count' => false,
        ],
    ]);

    $event = new FormSubmitted($this->form, [
        'submission_id' => 'sub_001',
        $field['id'] => 'Alice',
    ]);

    $handler = new MicrosoftTeamsIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        $body = $request->data()['attachments'][0]['content']['body'];
        return str_contains($body[0]['text'], 'Alice');
    });
});

it('escapes html in submission data', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'microsoft_teams',
        'data' => [
            'teams_webhook_url' => 'https://test.webhook.office.com/webhookb2/test123',
            'include_submission_data' => true,
        ],
    ]);

    $event = new FormSubmitted($this->form, [
        'submission_id' => 'sub_001',
        $this->form->properties[0]['id'] => '<script>alert("xss")</script>',
    ]);

    $handler = new MicrosoftTeamsIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        $body = json_encode($request->data());
        return ! str_contains($body, '<script>') && str_contains($body, '&lt;script&gt;');
    });
});
