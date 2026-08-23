<?php

use App\Events\Forms\FormSubmitted;
use App\Integrations\Handlers\NotionIntegration;
use App\Integrations\Notion\NotionApiClient;
use App\Models\Integration\FormIntegration;
use App\Models\OAuthProvider;
use Illuminate\Support\Facades\Http;

uses(\Tests\TestCase::class);

it('skips handling when no oauth_id is set', function () {
    Http::fake();

    $user = $this->actingAsProUser();
    $workspace = $this->createUserWorkspace($user);
    $form = $this->createForm($user, $workspace);

    $provider = OAuthProvider::factory()->for($user)->create([
        'provider' => 'notion',
        'access_token' => 'ntn_test_token',
    ]);

    $integration = FormIntegration::factory()->for($form)->for($provider, 'provider')->createQuietly([
        'integration_id' => 'notion',
        'data' => [
            'database_id' => 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
            'columns' => [],
        ],
    ]);

    $submissionData = [
        'submission_id' => 'sub_001',
        $form->properties[0]['id'] => 'Test Name',
    ];

    $event = new FormSubmitted($form, $submissionData);

    $integration->updateQuietly(['oauth_id' => null]);

    $handler = new NotionIntegration($event, $integration->fresh(['provider']), $integration->toArray());
    $handler->handle();

    Http::assertNothingSent();
});

it('skips handling when no database_id is set', function () {
    Http::fake();

    $user = $this->actingAsProUser();
    $workspace = $this->createUserWorkspace($user);
    $form = $this->createForm($user, $workspace);

    $provider = OAuthProvider::factory()->for($user)->create([
        'provider' => 'notion',
        'access_token' => 'ntn_test_token',
    ]);

    $integration = FormIntegration::factory()->for($form)->for($provider, 'provider')->create([
        'integration_id' => 'notion',
        'data' => [
            'columns' => [],
        ],
    ]);

    $submissionData = [
        'submission_id' => 'sub_001',
        $form->properties[0]['id'] => 'Test Name',
    ];

    $event = new FormSubmitted($form, $submissionData);
    $handler = new NotionIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertNothingSent();
});

it('formats and pushes submission to Notion', function () {
    Http::fake();

    $user = $this->actingAsProUser();
    $workspace = $this->createUserWorkspace($user);
    $form = $this->createForm($user, $workspace);

    $provider = OAuthProvider::factory()->for($user)->create([
        'provider' => 'notion',
        'access_token' => 'ntn_test_token',
    ]);

    $nameField = collect($form->properties)->firstWhere('name', 'Name');
    $databaseId = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';

    $integration = FormIntegration::factory()->for($form)->for($provider, 'provider')->create([
        'integration_id' => 'notion',
        'data' => [
            'database_id' => $databaseId,
            'columns' => [
                [
                    'id' => $nameField['id'],
                    'name' => 'Name',
                    'notion_property' => 'Name',
                    'notion_type' => 'rich_text',
                ],
            ],
        ],
    ]);

    $submissionData = [
        'submission_id' => 'sub_001',
        $nameField['id'] => 'Alice Smith',
    ];

    $event = new FormSubmitted($form, $submissionData);
    $handler = new NotionIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.notion.com/v1/pages'
            && $request->method() === 'POST'
            && $request->data()['parent']['database_id'] === 'a1b2c3d4-e5f6-7890-abcd-ef1234567890'
            && isset($request->data()['properties']['Name']['rich_text']);
    });
});

it('adds title property when database has a title column and no title is mapped', function () {
    Http::fake();

    $user = $this->actingAsProUser();
    $workspace = $this->createUserWorkspace($user);
    $form = $this->createForm($user, $workspace);

    $provider = OAuthProvider::factory()->for($user)->create([
        'provider' => 'notion',
        'access_token' => 'ntn_test_token',
    ]);

    $nameField = collect($form->properties)->firstWhere('name', 'Name');
    $databaseId = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';

    $integration = FormIntegration::factory()->for($form)->for($provider, 'provider')->create([
        'integration_id' => 'notion',
        'data' => [
            'database_id' => $databaseId,
            'schema' => [
                ['name' => 'Title', 'type' => 'title', 'id' => 'prop_title'],
                ['name' => 'Name', 'type' => 'rich_text', 'id' => 'prop_name'],
            ],
            'columns' => [
                [
                    'id' => $nameField['id'],
                    'name' => 'Name',
                    'notion_property' => 'Name',
                    'notion_type' => 'rich_text',
                ],
            ],
        ],
    ]);

    $submissionData = [
        'submission_id' => 'sub_042',
        $nameField['id'] => 'Alice',
    ];

    $event = new FormSubmitted($form, $submissionData);
    $handler = new NotionIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        if ($request->url() !== 'https://api.notion.com/v1/pages') {
            return false;
        }
        $props = $request->data()['properties'];
        return isset($props['Title']['title'])
            && str_contains($props['Title']['title'][0]['text']['content'], 'Submission #');
    });
});

it('does not add title property when title is already mapped', function () {
    Http::fake();

    $user = $this->actingAsProUser();
    $workspace = $this->createUserWorkspace($user);
    $form = $this->createForm($user, $workspace);

    $provider = OAuthProvider::factory()->for($user)->create([
        'provider' => 'notion',
        'access_token' => 'ntn_test_token',
    ]);

    $nameField = collect($form->properties)->firstWhere('name', 'Name');
    $databaseId = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';

    $integration = FormIntegration::factory()->for($form)->for($provider, 'provider')->create([
        'integration_id' => 'notion',
        'data' => [
            'database_id' => $databaseId,
            'schema' => [
                ['name' => 'Title', 'type' => 'title', 'id' => 'prop_title'],
            ],
            'columns' => [
                [
                    'id' => $nameField['id'],
                    'name' => 'Name',
                    'notion_property' => 'Title',
                    'notion_type' => 'title',
                ],
            ],
        ],
    ]);

    $submissionData = [
        'submission_id' => 'sub_042',
        $nameField['id'] => 'My Title',
    ];

    $event = new FormSubmitted($form, $submissionData);
    $handler = new NotionIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        if ($request->url() !== 'https://api.notion.com/v1/pages') {
            return false;
        }
        $props = $request->data()['properties'];
        return $props['Title']['title'][0]['text']['content'] === 'My Title';
    });
});

it('skips auto-title when database schema has no title column', function () {
    Http::fake();

    $user = $this->actingAsProUser();
    $workspace = $this->createUserWorkspace($user);
    $form = $this->createForm($user, $workspace);

    $provider = OAuthProvider::factory()->for($user)->create([
        'provider' => 'notion',
        'access_token' => 'ntn_test_token',
    ]);

    $nameField = collect($form->properties)->firstWhere('name', 'Name');
    $databaseId = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';

    $integration = FormIntegration::factory()->for($form)->for($provider, 'provider')->create([
        'integration_id' => 'notion',
        'data' => [
            'database_id' => $databaseId,
            'schema' => [
                ['name' => 'Name', 'type' => 'rich_text', 'id' => 'prop_name'],
            ],
            'columns' => [
                [
                    'id' => $nameField['id'],
                    'name' => 'Name',
                    'notion_property' => 'Name',
                    'notion_type' => 'rich_text',
                ],
            ],
        ],
    ]);

    $submissionData = [
        'submission_id' => 'sub_042',
        $nameField['id'] => 'Alice',
    ];

    $event = new FormSubmitted($form, $submissionData);
    $handler = new NotionIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        if ($request->url() !== 'https://api.notion.com/v1/pages') {
            return false;
        }
        $props = $request->data()['properties'];
        return count($props) === 1 && isset($props['Name']);
    });
});

it('returns correct validation rules', function () {
    $rules = NotionIntegration::getValidationRules(null);

    expect($rules)->toHaveKey('database_id');
    expect($rules['database_id'])->toContain('required');
    expect($rules['database_id'])->toContain('string');
});

it('requires OAuth', function () {
    expect(NotionIntegration::isOAuthRequired())->toBeTrue();
});

it('returns correct validation attributes', function () {
    $attributes = NotionIntegration::getValidationAttributes();

    expect($attributes)->toHaveKey('oauth_id');
    expect($attributes)->toHaveKey('data.database_id');
    expect($attributes['oauth_id'])->toBe('Notion Workspace');
    expect($attributes['data.database_id'])->toBe('Notion Database');
});

it('formats submission data using FormSubmissionFormatter', function () {
    Http::fake();

    $user = $this->actingAsProUser();
    $workspace = $this->createUserWorkspace($user);
    $form = $this->createForm($user, $workspace);

    $provider = OAuthProvider::factory()->for($user)->create([
        'provider' => 'notion',
        'access_token' => 'ntn_test_token',
    ]);

    $nameField = collect($form->properties)->firstWhere('name', 'Name');
    $databaseId = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';

    $integration = FormIntegration::factory()->for($form)->for($provider, 'provider')->create([
        'integration_id' => 'notion',
        'data' => [
            'database_id' => $databaseId,
            'columns' => [
                [
                    'id' => $nameField['id'],
                    'name' => 'Name',
                    'notion_property' => 'Name',
                    'notion_type' => 'rich_text',
                ],
            ],
        ],
    ]);

    $submissionData = [
        'submission_id' => 'sub_001',
        $nameField['id'] => '<script>alert("xss")</script>',
    ];

    $event = new FormSubmitted($form, $submissionData);
    $handler = new NotionIntegration($event, $integration, $integration->toArray());
    $handler->handle();

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.notion.com/v1/pages';
    });
});

it('handles API error gracefully through run method', function () {
    $user = $this->actingAsProUser();
    $workspace = $this->createUserWorkspace($user);
    $form = $this->createForm($user, $workspace);

    $provider = OAuthProvider::factory()->for($user)->create([
        'provider' => 'notion',
        'access_token' => 'ntn_test_token',
    ]);

    $nameField = collect($form->properties)->firstWhere('name', 'Name');
    $databaseId = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';

    $integration = FormIntegration::factory()->for($form)->for($provider, 'provider')->create([
        'integration_id' => 'notion',
        'data' => [
            'database_id' => $databaseId,
            'columns' => [
                [
                    'id' => $nameField['id'],
                    'name' => 'Name',
                    'notion_property' => 'Name',
                    'notion_type' => 'rich_text',
                ],
            ],
        ],
    ]);

    Http::fake([
        'https://api.notion.com/v1/pages' => Http::response(['message' => 'Validation error'], 400),
    ]);

    $submissionData = [
        'submission_id' => 'sub_001',
        $nameField['id'] => 'Alice',
    ];

    $event = new FormSubmitted($form, $submissionData);
    $handler = new NotionIntegration($event, $integration, $integration->toArray());

    $handler->run();

    expect($integration->events()->where('status', 'error')->count())->toBeGreaterThanOrEqual(1);
});
