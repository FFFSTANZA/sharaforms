<?php

use App\Integrations\Handlers\Events\NotionIntegrationCreated;
use App\Models\Integration\FormIntegration;
use App\Models\OAuthProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

it('fetches and stores database schema on creation', function () {
    $user = $this->actingAsProUser();
    $workspace = $this->createUserWorkspace($user);
    $form = $this->createForm($user, $workspace);

    $provider = OAuthProvider::factory()->for($user)->create([
        'provider' => 'notion',
        'access_token' => 'ntn_test_token',
    ]);

    $databaseId = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';

    $integration = FormIntegration::factory()->for($form)->for($provider, 'provider')->create([
        'integration_id' => 'notion',
        'data' => [
            'database_id' => $databaseId,
        ],
    ]);

    $fakeDbResponse = [
        'id' => $databaseId,
        'url' => 'https://notion.so/my-database',
        'properties' => [
            'Name' => ['id' => 'prop1', 'type' => 'title', 'name' => 'Name'],
            'Email' => ['id' => 'prop2', 'type' => 'email', 'name' => 'Email'],
        ],
    ];

    Http::fake([
        "https://api.notion.com/v1/databases/{$databaseId}" => Http::response($fakeDbResponse, 200),
    ]);

    $eventHandler = new NotionIntegrationCreated($integration);
    $eventHandler->handle();

    $integration->refresh();

    expect($integration->data->schema)->toHaveCount(2);
    expect($integration->data->database_url)->toBe('https://notion.so/my-database');
    expect($integration->data->columns)->not->toBeEmpty();
});

it('auto-matches form fields to Notion properties by name', function () {
    $user = $this->actingAsProUser();
    $workspace = $this->createUserWorkspace($user);
    $form = $this->createForm($user, $workspace);

    $provider = OAuthProvider::factory()->for($user)->create([
        'provider' => 'notion',
        'access_token' => 'ntn_test_token',
    ]);

    $databaseId = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';

    $integration = FormIntegration::factory()->for($form)->for($provider, 'provider')->create([
        'integration_id' => 'notion',
        'data' => [
            'database_id' => $databaseId,
        ],
    ]);

    $fakeDbResponse = [
        'id' => $databaseId,
        'url' => 'https://notion.so/my-db',
        'properties' => [
            'Name' => ['id' => 'prop1', 'type' => 'title', 'name' => 'Name'],
            'Email' => ['id' => 'prop2', 'type' => 'email', 'name' => 'Email'],
        ],
    ];

    Http::fake([
        "https://api.notion.com/v1/databases/{$databaseId}" => Http::response($fakeDbResponse, 200),
    ]);

    $eventHandler = new NotionIntegrationCreated($integration);
    $eventHandler->handle();

    $integration->refresh();

    $nameField = collect($form->properties)->firstWhere('name', 'Name');
    $nameColumn = collect($integration->data->columns)->firstWhere('id', $nameField['id']);
    expect($nameColumn)->not->toBeNull();
    $nameColumnArr = (array) $nameColumn;
    expect($nameColumnArr['notion_property'])->toBe('Name');
    expect($nameColumnArr['notion_type'])->toBe('title');
});

it('skips internal nf- prefixed fields in column mapping', function () {
    $user = $this->actingAsProUser();
    $workspace = $this->createUserWorkspace($user);

    $form = $this->createForm($user, $workspace);
    $form->update([
        'properties' => array_merge($form->properties, [
            [
                'id' => 'nf_001',
                'name' => 'Internal Field',
                'type' => 'nf-text',
                'hidden' => true,
            ],
        ]),
    ]);

    $provider = OAuthProvider::factory()->for($user)->create([
        'provider' => 'notion',
        'access_token' => 'ntn_test_token',
    ]);

    $databaseId = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';

    $integration = FormIntegration::factory()->for($form)->for($provider, 'provider')->create([
        'integration_id' => 'notion',
        'data' => [
            'database_id' => $databaseId,
        ],
    ]);

    $fakeDbResponse = [
        'id' => $databaseId,
        'url' => 'https://notion.so/my-db',
        'properties' => [
            'Name' => ['id' => 'prop1', 'type' => 'title', 'name' => 'Name'],
        ],
    ];

    Http::fake([
        "https://api.notion.com/v1/databases/{$databaseId}" => Http::response($fakeDbResponse, 200),
    ]);

    $eventHandler = new NotionIntegrationCreated($integration);
    $eventHandler->handle();

    $integration->refresh();

    $nfColumn = collect($integration->data->columns)->firstWhere('id', 'nf_001');
    expect($nfColumn)->toBeNull();
});

it('uses rich_text as default Notion type when no match is found', function () {
    $user = $this->actingAsProUser();
    $workspace = $this->createUserWorkspace($user);
    $form = $this->createForm($user, $workspace);

    $provider = OAuthProvider::factory()->for($user)->create([
        'provider' => 'notion',
        'access_token' => 'ntn_test_token',
    ]);

    $databaseId = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';

    $integration = FormIntegration::factory()->for($form)->for($provider, 'provider')->create([
        'integration_id' => 'notion',
        'data' => [
            'database_id' => $databaseId,
        ],
    ]);

    $fakeDbResponse = [
        'id' => $databaseId,
        'url' => 'https://notion.so/db',
        'properties' => [
            'Not Name' => ['id' => 'prop1', 'type' => 'rich_text', 'name' => 'Not Name'],
        ],
    ];

    Http::fake([
        "https://api.notion.com/v1/databases/{$databaseId}" => Http::response($fakeDbResponse, 200),
    ]);

    $eventHandler = new NotionIntegrationCreated($integration);
    $eventHandler->handle();

    $integration->refresh();

    foreach ($integration->data->columns as $column) {
        expect(((array) $column)['notion_type'])->toBe('rich_text');
    }
});

it('logs warning when no OAuth provider is attached', function () {
    Http::fake();

    $user = $this->actingAsProUser();
    $workspace = $this->createUserWorkspace($user);
    $form = $this->createForm($user, $workspace);

    $integration = FormIntegration::factory()->for($form)->create([
        'integration_id' => 'notion',
        'data' => [
            'database_id' => 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
        ],
    ]);

    Log::shouldReceive('warning')
        ->once()
        ->with('Notion integration created without an OAuth provider', \Mockery::type('array'));

    $eventHandler = new NotionIntegrationCreated($integration);
    $eventHandler->handle();

    Http::assertNothingSent();
});

it('skips database fetch when no database_id is set', function () {
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
        'data' => [],
    ]);

    $eventHandler = new NotionIntegrationCreated($integration);
    $eventHandler->handle();

    Http::assertNothingSent();
});

it('handles API errors gracefully during schema fetch', function () {
    $user = $this->actingAsProUser();
    $workspace = $this->createUserWorkspace($user);
    $form = $this->createForm($user, $workspace);

    $provider = OAuthProvider::factory()->for($user)->create([
        'provider' => 'notion',
        'access_token' => 'ntn_test_token',
    ]);

    $databaseId = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';

    $integration = FormIntegration::factory()->for($form)->for($provider, 'provider')->create([
        'integration_id' => 'notion',
        'data' => [
            'database_id' => $databaseId,
        ],
    ]);

    Http::fake([
        "https://api.notion.com/v1/databases/{$databaseId}" => Http::response(['message' => 'Object Not Found'], 404),
    ]);

    Log::shouldReceive('error')
        ->once()
        ->with('Failed to initialize Notion integration', \Mockery::type('array'));

    $eventHandler = new NotionIntegrationCreated($integration);
    $eventHandler->handle();

    $integration->refresh();
    expect($integration->data->schema ?? null)->toBeNull();
});

it('preserves existing integration data when merging', function () {
    $user = $this->actingAsProUser();
    $workspace = $this->createUserWorkspace($user);
    $form = $this->createForm($user, $workspace);

    $provider = OAuthProvider::factory()->for($user)->create([
        'provider' => 'notion',
        'access_token' => 'ntn_test_token',
    ]);

    $databaseId = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';

    $integration = FormIntegration::factory()->for($form)->for($provider, 'provider')->create([
        'integration_id' => 'notion',
        'data' => [
            'database_id' => $databaseId,
            'some_custom_field' => 'custom_value',
        ],
    ]);

    $fakeDbResponse = [
        'id' => $databaseId,
        'url' => 'https://notion.so/db',
        'properties' => [
            'Name' => ['id' => 'prop1', 'type' => 'title', 'name' => 'Name'],
        ],
    ];

    Http::fake([
        "https://api.notion.com/v1/databases/{$databaseId}" => Http::response($fakeDbResponse, 200),
    ]);

    $eventHandler = new NotionIntegrationCreated($integration);
    $eventHandler->handle();

    $integration->refresh();

    expect($integration->data->some_custom_field)->toBe('custom_value');
    expect($integration->data->schema)->not->toBeEmpty();
    expect($integration->data->database_url)->toBe('https://notion.so/db');
});
