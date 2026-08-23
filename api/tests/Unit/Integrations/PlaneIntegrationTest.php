<?php

use App\Events\Forms\FormSubmitted;
use App\Integrations\Handlers\PlaneIntegration;
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

function planeIssuePayload(): array
{
    return Http::recorded()
        ->map(fn ($pair) => $pair[0]->data())
        ->first() ?? [];
}

it('creates an issue in the workspace project with api key header', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'plane',
        'data' => [
            'api_key' => 'plane_api_key_123',
            'workspace_slug' => 'acme',
            'project_id' => 'proj-uuid',
            'priority' => 'high',
            'state_id' => 'state-uuid',
        ],
    ]);

    $event = new FormSubmitted($this->form, [
        'submission_id' => 'sub_001',
        $this->form->properties[0]['id'] => 'Alice',
    ]);

    $handler = new PlaneIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        return str_starts_with($request->url(), 'https://api.plane.so/api/v1/workspaces/acme/projects/proj-uuid/issues/')
            && $request->method() === 'POST'
            && $request->hasHeader('X-API-Key')
            && $request->header('X-API-Key')[0] === 'plane_api_key_123';
    });

    $payload = planeIssuePayload();
    expect($payload['name'])->toBe($this->form->title.' - Submission')
        ->and($payload['priority'])->toBe('high')
        ->and($payload['state_id'])->toBe('state-uuid')
        ->and($payload['description_html'])->toContain($this->form->properties[0]['name'].': Alice');
});

it('uses custom base url for self-hosted instances', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'plane',
        'data' => [
            'api_key' => 'plane_api_key_123',
            'base_url' => 'https://api.plane.example.com/',
            'workspace_slug' => 'acme',
            'project_id' => 'proj-uuid',
        ],
    ]);

    $event = new FormSubmitted($this->form, ['submission_id' => 'sub_001']);
    $handler = new PlaneIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        return str_starts_with($request->url(), 'https://api.plane.example.com/api/v1/workspaces/acme/projects/proj-uuid/issues/');
    });
});

it('skips when project id is missing', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'plane',
        'data' => [
            'api_key' => 'plane_api_key_123',
            'workspace_slug' => 'acme',
        ],
    ]);

    $event = new FormSubmitted($this->form, ['submission_id' => 'sub_001']);
    $handler = new PlaneIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertNothingSent();
});

it('returns correct validation rules', function () {
    $rules = PlaneIntegration::getValidationRules($this->form);

    expect($rules)->toHaveKey('api_key')
        ->and($rules)->toHaveKey('base_url')
        ->and($rules)->toHaveKey('workspace_slug')
        ->and($rules)->toHaveKey('project_id')
        ->and($rules)->toHaveKey('state_id')
        ->and($rules)->toHaveKey('priority')
        ->and($rules)->toHaveKey('issue_title_template')
        ->and($rules)->toHaveKey('description_template');
});
