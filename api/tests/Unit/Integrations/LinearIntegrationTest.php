<?php

use App\Events\Forms\FormSubmitted;
use App\Integrations\Handlers\LinearIntegration;
use App\Models\Integration\FormIntegration;
use Illuminate\Support\Facades\Http;

uses(\Tests\TestCase::class);

beforeEach(function () {
    Http::fake();
    $user = $this->actingAsProUser();
    $this->workspace = $this->createUserWorkspace($user);
    $this->form = $this->createForm($user, $this->workspace);
});

function linearIssueInput(): array
{
    return Http::recorded()
        ->map(fn ($pair) => $pair[0]->data()['variables']['input'] ?? null)
        ->filter()
        ->first() ?? [];
}

it('creates an issue via graphql with raw authorization header', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'linear',
        'data' => [
            'api_key' => 'lin_api_key_123',
            'team_id' => 'team-uuid',
        ],
    ]);

    $event = new FormSubmitted($this->form, [
        'submission_id' => 'sub_001',
        $this->form->properties[0]['id'] => 'Alice',
    ]);

    $handler = new LinearIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.linear.app/graphql'
            && $request->method() === 'POST'
            && $request->header('Authorization')[0] === 'lin_api_key_123'
            && str_contains($request->data()['query'], 'issueCreate');
    });

    $input = linearIssueInput();
    expect($input['teamId'])->toBe('team-uuid')
        ->and($input['title'])->toBe($this->form->title.' - Submission')
        ->and($input['description'])->toContain('**'.$this->form->properties[0]['name'].':** Alice');
});

it('applies title template, priority and routing', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'linear',
        'data' => [
            'api_key' => 'lin_api_key_123',
            'team_id' => 'team-uuid',
            'title_template' => 'New lead for Acme',
            'project_id' => 'proj-uuid',
            'state_id' => 'state-uuid',
            'priority' => 2,
            'views_submissions_count' => false,
            'link_open_form' => false,
            'link_edit_form' => false,
            'include_submission_data' => false,
        ],
    ]);

    $event = new FormSubmitted($this->form, ['submission_id' => 'sub_001']);
    $handler = new LinearIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    $input = linearIssueInput();
    expect($input['title'])->toBe('New lead for Acme')
        ->and($input['projectId'])->toBe('proj-uuid')
        ->and($input['stateId'])->toBe('state-uuid')
        ->and($input['priority'])->toBe(2)
        ->and($input['description'])->not->toContain(':');
});

it('skips when no api key is set', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'linear',
        'data' => ['team_id' => 'team-uuid'],
    ]);

    $event = new FormSubmitted($this->form, ['submission_id' => 'sub_001']);
    $handler = new LinearIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertNothingSent();
});

it('skips when no team id is set', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'linear',
        'data' => ['api_key' => 'lin_api_key_123'],
    ]);

    $event = new FormSubmitted($this->form, ['submission_id' => 'sub_001']);
    $handler = new LinearIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertNothingSent();
});

it('applies labels when set', function () {
    $integration = FormIntegration::factory()->for($this->form)->createQuietly([
        'integration_id' => 'linear',
        'data' => [
            'api_key' => 'lin_api_key_123',
            'team_id' => 'team-uuid',
            'label_ids' => ' label-1 , label-2 ,',
            'include_submission_data' => false,
            'views_submissions_count' => false,
            'link_open_form' => false,
            'link_edit_form' => false,
        ],
    ]);

    $event = new FormSubmitted($this->form, ['submission_id' => 'sub_001']);
    $handler = new LinearIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    $input = linearIssueInput();
    expect($input['labelIds'])->toBe(['label-1', 'label-2']);
});

it('returns correct validation rules', function () {
    $rules = LinearIntegration::getValidationRules($this->form);

    expect($rules)->toHaveKey('api_key')
        ->and($rules)->toHaveKey('team_id')
        ->and($rules)->toHaveKey('project_id')
        ->and($rules)->toHaveKey('state_id')
        ->and($rules)->toHaveKey('title_template')
        ->and($rules)->toHaveKey('description_template')
        ->and($rules)->toHaveKey('priority');
});
