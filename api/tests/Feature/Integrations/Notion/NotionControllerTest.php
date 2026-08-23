<?php

use App\Models\OAuthProvider;
use Illuminate\Support\Facades\Http;

it('returns 404 when no Notion provider exists for the user', function () {
    $user = $this->actingAsProUser();

    $this->getJson(route('open.notion.databases'))
        ->assertStatus(404)
        ->assertJson([
            'message' => 'No connected Notion workspace found. Please connect your Notion account first.',
        ]);
});

it('returns 404 when querying a specific oauth_id that does not exist', function () {
    $user = $this->actingAsProUser();

    $this->getJson(route('open.notion.databases') . '?oauth_id=999999')
        ->assertStatus(404);
});

it('lists Notion databases for the authenticated user', function () {
    $user = $this->actingAsProUser();

    OAuthProvider::factory()->for($user)->create([
        'provider' => 'notion',
        'access_token' => 'ntn_test_token',
    ]);

    $fakeResponse = [
        'results' => [
            [
                'id' => 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
                'title' => [['plain_text' => 'Project Tracker']],
                'icon' => ['emoji' => '🚀'],
                'url' => 'https://notion.so/project-tracker',
                'last_edited_time' => '2026-01-15T10:00:00.000Z',
            ],
        ],
    ];

    Http::fake([
        'https://api.notion.com/v1/search' => Http::response($fakeResponse, 200),
    ]);

    $this->getJson(route('open.notion.databases'))
        ->assertSuccessful()
        ->assertJsonCount(1)
        ->assertJson([
            [
                'id' => 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
                'title' => 'Project Tracker',
                'icon' => '🚀',
                'url' => 'https://notion.so/project-tracker',
            ],
        ]);
});

it('returns database properties for a valid database ID', function () {
    $user = $this->actingAsProUser();

    OAuthProvider::factory()->for($user)->create([
        'provider' => 'notion',
        'access_token' => 'ntn_test_token',
    ]);

    $dbId = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
    $fakeResponse = [
        'id' => $dbId,
        'properties' => [
            'Name' => ['id' => 'prop1', 'type' => 'title', 'name' => 'Name'],
            'Status' => ['id' => 'prop2', 'type' => 'select', 'name' => 'Status'],
            'Due Date' => ['id' => 'prop3', 'type' => 'date', 'name' => 'Due Date'],
        ],
    ];

    Http::fake([
        "https://api.notion.com/v1/databases/{$dbId}" => Http::response($fakeResponse, 200),
    ]);

    $this->getJson(route('open.notion.database-properties', $dbId))
        ->assertSuccessful()
        ->assertJsonCount(3)
        ->assertJson([
            ['name' => 'Name', 'type' => 'title', 'id' => 'prop1'],
            ['name' => 'Status', 'type' => 'select', 'id' => 'prop2'],
        ]);
});

it('returns 404 when querying another user\'s Notion provider by oauth_id', function () {
    $userA = $this->createProUser();
    $this->actingAs($userA, 'api');

    $providerA = OAuthProvider::factory()->for($userA)->create([
        'provider' => 'notion',
        'access_token' => 'ntn_token_a',
    ]);

    $userB = $this->createProUser();
    $this->actingAs($userB, 'api');

    $this->getJson(route('open.notion.databases') . '?oauth_id=' . $providerA->id)
        ->assertStatus(404);
});

it('scopes Notion provider queries to the authenticated user', function () {
    $userA = $this->createProUser();
    $this->actingAs($userA, 'api');

    OAuthProvider::factory()->for($userA)->create([
        'provider' => 'notion',
        'access_token' => 'ntn_token_a',
    ]);

    $userB = $this->createProUser();
    $this->actingAs($userB, 'api');

    $this->getJson(route('open.notion.databases'))
        ->assertStatus(404);
});

it('returns empty list when user has Notion provider but workspace has no databases', function () {
    $user = $this->actingAsProUser();

    OAuthProvider::factory()->for($user)->create([
        'provider' => 'notion',
        'access_token' => 'ntn_test_token',
    ]);

    Http::fake([
        'https://api.notion.com/v1/search' => Http::response(['results' => []], 200),
    ]);

    $this->getJson(route('open.notion.databases'))
        ->assertSuccessful()
        ->assertJsonCount(0);
});

it('returns 500 for an invalid database UUID format', function () {
    $user = $this->actingAsProUser();

    OAuthProvider::factory()->for($user)->create([
        'provider' => 'notion',
        'access_token' => 'ntn_test_token',
    ]);

    $this->getJson(route('open.notion.database-properties', 'not-a-valid-uuid'))
        ->assertStatus(500);
});

it('uses the first Notion provider when no oauth_id is specified', function () {
    $user = $this->actingAsProUser();

    $provider = OAuthProvider::factory()->for($user)->create([
        'provider' => 'notion',
        'access_token' => 'ntn_first_token',
    ]);

    $dbId = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';

    $fakeResponse = [
        'id' => $dbId,
        'properties' => [
            'Name' => ['id' => 'prop1', 'type' => 'title', 'name' => 'Name'],
        ],
    ];

    Http::fake([
        "https://api.notion.com/v1/databases/{$dbId}" => Http::response($fakeResponse, 200),
    ]);

    $this->getJson(route('open.notion.database-properties', $dbId))
        ->assertSuccessful()
        ->assertJsonCount(1);
});
