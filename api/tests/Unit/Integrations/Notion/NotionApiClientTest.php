<?php

use App\Integrations\Notion\NotionApiClient;
use App\Models\OAuthProvider;
use Illuminate\Support\Facades\Http;

uses(\Tests\TestCase::class);

it('formats a rich_text property value', function () {
    $columns = [
        ['id' => 'f1', 'name' => 'Name', 'notion_property' => 'Name', 'notion_type' => 'rich_text'],
    ];
    $submission = ['f1' => 'Alice'];

    $result = NotionApiClient::formatProperties($columns, $submission);

    expect($result)->toHaveKey('Name');
    expect($result['Name']['rich_text'][0]['text']['content'])->toBe('Alice');
});

it('formats a title property value', function () {
    $columns = [
        ['id' => 'f1', 'name' => 'Subject', 'notion_property' => 'Subject', 'notion_type' => 'title'],
    ];
    $submission = ['f1' => 'Hello'];

    $result = NotionApiClient::formatProperties($columns, $submission);

    expect($result['Subject']['title'][0]['text']['content'])->toBe('Hello');
});

it('formats a number property value', function () {
    $columns = [
        ['id' => 'f1', 'name' => 'Count', 'notion_property' => 'Count', 'notion_type' => 'number'],
    ];
    $submission = ['f1' => '42'];

    $result = NotionApiClient::formatProperties($columns, $submission);

    expect($result['Count'])->toBe(['number' => 42.0]);
});

it('returns null for non-numeric value on number type', function () {
    $columns = [
        ['id' => 'f1', 'name' => 'Count', 'notion_property' => 'Count', 'notion_type' => 'number'],
    ];
    $submission = ['f1' => 'not-a-number'];

    $result = NotionApiClient::formatProperties($columns, $submission);

    expect($result)->not->toHaveKey('Count');
});

it('formats an email property value', function () {
    $columns = [
        ['id' => 'f1', 'name' => 'Email', 'notion_property' => 'Email', 'notion_type' => 'email'],
    ];
    $submission = ['f1' => 'test@example.com'];

    $result = NotionApiClient::formatProperties($columns, $submission);

    expect($result['Email'])->toBe(['email' => 'test@example.com']);
});

it('formats a url property value', function () {
    $columns = [
        ['id' => 'f1', 'name' => 'Website', 'notion_property' => 'Website', 'notion_type' => 'url'],
    ];
    $submission = ['f1' => 'https://example.com'];

    $result = NotionApiClient::formatProperties($columns, $submission);

    expect($result['Website'])->toBe(['url' => 'https://example.com']);
});

it('formats a date property value', function () {
    $columns = [
        ['id' => 'f1', 'name' => 'Due', 'notion_property' => 'Due', 'notion_type' => 'date'],
    ];
    $submission = ['f1' => '2026-01-15'];

    $result = NotionApiClient::formatProperties($columns, $submission);

    expect($result['Due'])->toBe(['date' => ['start' => '2026-01-15']]);
});

it('formats a checkbox property value (truthy)', function () {
    $columns = [
        ['id' => 'f1', 'name' => 'Active', 'notion_property' => 'Active', 'notion_type' => 'checkbox'],
    ];

    foreach (['yes', 'true', '1', 'on'] as $value) {
        $result = NotionApiClient::formatProperties($columns, ['f1' => $value]);
        expect($result['Active'])->toBe(['checkbox' => true]);
    }
});

it('formats a checkbox property value (falsy)', function () {
    $columns = [
        ['id' => 'f1', 'name' => 'Active', 'notion_property' => 'Active', 'notion_type' => 'checkbox'],
    ];

    foreach (['no', 'false', '0', 'off'] as $value) {
        $result = NotionApiClient::formatProperties($columns, ['f1' => $value]);
        expect($result['Active'])->toBe(['checkbox' => false]);
    }
});

it('formats a select property value', function () {
    $columns = [
        ['id' => 'f1', 'name' => 'Status', 'notion_property' => 'Status', 'notion_type' => 'select'],
    ];
    $submission = ['f1' => 'In Progress'];

    $result = NotionApiClient::formatProperties($columns, $submission);

    expect($result['Status'])->toBe(['select' => ['name' => 'In Progress']]);
});

it('formats a multi_select property value', function () {
    $columns = [
        ['id' => 'f1', 'name' => 'Tags', 'notion_property' => 'Tags', 'notion_type' => 'multi_select'],
    ];
    $submission = ['f1' => 'tag1, tag2, tag3'];

    $result = NotionApiClient::formatProperties($columns, $submission);

    expect($result['Tags']['multi_select'])->toHaveCount(3);
    expect($result['Tags']['multi_select'])->toBe([
        ['name' => 'tag1'],
        ['name' => 'tag2'],
        ['name' => 'tag3'],
    ]);
});

it('falls back to rich_text for unknown property type', function () {
    $columns = [
        ['id' => 'f1', 'name' => 'Custom', 'notion_property' => 'Custom', 'notion_type' => 'unknown_type'],
    ];
    $submission = ['f1' => 'some value'];

    $result = NotionApiClient::formatProperties($columns, $submission);

    expect($result['Custom']['rich_text'][0]['text']['content'])->toBe('some value');
});

it('skips null and empty string values', function () {
    $columns = [
        ['id' => 'f1', 'name' => 'A', 'notion_property' => 'A', 'notion_type' => 'rich_text'],
        ['id' => 'f2', 'name' => 'B', 'notion_property' => 'B', 'notion_type' => 'rich_text'],
    ];
    $submission = ['f1' => null, 'f2' => ''];

    $result = NotionApiClient::formatProperties($columns, $submission);

    expect($result)->toBeEmpty();
});

it('uses column name as fallback when notion_property is missing', function () {
    $columns = [
        ['id' => 'f1', 'name' => 'FieldName', 'notion_type' => 'rich_text'],
    ];
    $submission = ['f1' => 'value'];

    $result = NotionApiClient::formatProperties($columns, $submission);

    expect($result)->toHaveKey('FieldName');
});

it('truncates rich_text values to 2000 characters', function () {
    $columns = [
        ['id' => 'f1', 'name' => 'Long', 'notion_property' => 'Long', 'notion_type' => 'rich_text'],
    ];
    $longValue = str_repeat('a', 2500);
    $submission = ['f1' => $longValue];

    $result = NotionApiClient::formatProperties($columns, $submission);

    expect($result['Long']['rich_text'][0]['text']['content'])->toHaveLength(2000);
});

it('truncates title values to 2000 characters', function () {
    $columns = [
        ['id' => 'f1', 'name' => 'Title', 'notion_property' => 'Title', 'notion_type' => 'title'],
    ];
    $longValue = str_repeat('b', 2500);
    $submission = ['f1' => $longValue];

    $result = NotionApiClient::formatProperties($columns, $submission);

    expect($result['Title']['title'][0]['text']['content'])->toHaveLength(2000);
});

it('converts array values to comma-separated string', function () {
    $columns = [
        ['id' => 'f1', 'name' => 'Field', 'notion_property' => 'Field', 'notion_type' => 'rich_text'],
    ];
    $submission = ['f1' => ['one', 'two', 'three']];

    $result = NotionApiClient::formatProperties($columns, $submission);

    expect($result['Field']['rich_text'][0]['text']['content'])->toBe('one, two, three');
});

it('formats integer numeric values as float', function () {
    $columns = [
        ['id' => 'f1', 'name' => 'Count', 'notion_property' => 'Count', 'notion_type' => 'number'],
    ];
    $submission = ['f1' => 7];

    $result = NotionApiClient::formatProperties($columns, $submission);

    expect($result['Count'])->toBe(['number' => 7.0]);
});

it('formats negative numeric values correctly', function () {
    $columns = [
        ['id' => 'f1', 'name' => 'Temp', 'notion_property' => 'Temp', 'notion_type' => 'number'],
    ];
    $submission = ['f1' => '-3.5'];

    $result = NotionApiClient::formatProperties($columns, $submission);

    expect($result['Temp'])->toBe(['number' => -3.5]);
});

// --- API-calling tests (require a user for OAuthProvider factory) ---

it('accepts valid UUID database ID with hyphens', function () {
    $user = $this->createUser();
    $provider = OAuthProvider::factory()->for($user)->create([
        'provider' => 'notion',
        'access_token' => 'ntn_test_token',
    ]);

    Http::fake([
        'https://api.notion.com/v1/databases/*' => Http::response([], 200),
    ]);

    $client = new NotionApiClient($provider);
    $client->getDatabase('a1b2c3d4-e5f6-7890-abcd-ef1234567890');

    expect(true)->toBeTrue();
});

it('accepts valid UUID database ID without hyphens', function () {
    $user = $this->createUser();
    $provider = OAuthProvider::factory()->for($user)->create([
        'provider' => 'notion',
        'access_token' => 'ntn_test_token',
    ]);

    Http::fake([
        'https://api.notion.com/v1/databases/*' => Http::response([], 200),
    ]);

    $client = new NotionApiClient($provider);
    $client->getDatabase('a1b2c3d4e5f67890abcdef1234567890');

    expect(true)->toBeTrue();
});

it('throws exception for invalid database ID format', function () {
    $user = $this->createUser();
    $provider = OAuthProvider::factory()->for($user)->create([
        'provider' => 'notion',
        'access_token' => 'ntn_test_token',
    ]);

    $client = new NotionApiClient($provider);
    $client->getDatabase('not-a-uuid');
})->throws(Exception::class, 'Invalid Notion database ID format');

it('throws exception for database ID with invalid characters', function () {
    $user = $this->createUser();
    $provider = OAuthProvider::factory()->for($user)->create([
        'provider' => 'notion',
        'access_token' => 'ntn_test_token',
    ]);

    $client = new NotionApiClient($provider);
    $client->getDatabase('a1b2c3d4-e5f6-7890-abcd-ef123456789g');
})->throws(Exception::class, 'Invalid Notion database ID format');

it('caches database responses per instance', function () {
    $user = $this->createUser();
    $provider = OAuthProvider::factory()->for($user)->create([
        'provider' => 'notion',
        'access_token' => 'ntn_test_token',
    ]);

    $dbId = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
    $fakeResponse = [
        'id' => $dbId,
        'title' => [['plain_text' => 'Test DB']],
        'properties' => [
            'Name' => ['id' => 'prop1', 'type' => 'title', 'name' => 'Name'],
        ],
    ];

    $callCount = 0;
    Http::fake(function ($request) use (&$callCount, $fakeResponse) {
        if (str_contains($request->url(), '/databases/')) {
            $callCount++;
            return Http::response($fakeResponse, 200);
        }
        return Http::response([], 200);
    });

    $client = new NotionApiClient($provider);

    $result1 = $client->getDatabase($dbId);
    $result2 = $client->getDatabase($dbId);

    expect($result1)->toBe($result2);
    expect($callCount)->toBe(1);
});

it('extracts plain text from rich_text array for listDatabases', function () {
    $user = $this->createUser();
    $provider = OAuthProvider::factory()->for($user)->create([
        'provider' => 'notion',
        'access_token' => 'ntn_test_token',
    ]);

    $fakeResponse = [
        'results' => [
            [
                'id' => 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
                'title' => [
                    ['plain_text' => 'My '],
                    ['plain_text' => 'Database'],
                ],
                'icon' => ['emoji' => '📋'],
                'url' => 'https://notion.so/my-db',
                'last_edited_time' => '2026-01-01T00:00:00.000Z',
            ],
        ],
    ];

    Http::fake([
        'https://api.notion.com/v1/search' => Http::response($fakeResponse, 200),
    ]);

    $client = new NotionApiClient($provider);
    $databases = $client->listDatabases();

    expect($databases)->toHaveCount(1);
    expect($databases[0]['title'])->toBe('My Database');
    expect($databases[0]['icon'])->toBe('📋');
    expect($databases[0]['url'])->toBe('https://notion.so/my-db');
});

it('returns empty array when listDatabases has no results', function () {
    $user = $this->createUser();
    $provider = OAuthProvider::factory()->for($user)->create([
        'provider' => 'notion',
        'access_token' => 'ntn_test_token',
    ]);

    Http::fake([
        'https://api.notion.com/v1/search' => Http::response(['results' => []], 200),
    ]);

    $client = new NotionApiClient($provider);
    $databases = $client->listDatabases();

    expect($databases)->toBeEmpty();
});

it('throws exception on failed API request', function () {
    $user = $this->createUser();
    $provider = OAuthProvider::factory()->for($user)->create([
        'provider' => 'notion',
        'access_token' => 'ntn_test_token',
    ]);

    Http::fake([
        'https://api.notion.com/v1/databases/*' => Http::response(['message' => 'Invalid token'], 401),
    ]);

    $client = new NotionApiClient($provider);
    $client->getDatabase('a1b2c3d4-e5f6-7890-abcd-ef1234567890');
})->throws(Exception::class, 'Notion API error: Invalid token');

it('throws exception on unsupported HTTP method', function () {
    $user = $this->createUser();
    $provider = OAuthProvider::factory()->for($user)->create([
        'provider' => 'notion',
        'access_token' => 'ntn_test_token',
    ]);

    $reflection = new ReflectionClass(NotionApiClient::class);
    $method = $reflection->getMethod('request');
    $method->setAccessible(true);

    $client = new NotionApiClient($provider);
    $method->invoke($client, 'DELETE', '/test');
})->throws(Exception::class, 'Unsupported HTTP method: DELETE');

it('handles listDatabases with missing title gracefully', function () {
    $user = $this->createUser();
    $provider = OAuthProvider::factory()->for($user)->create([
        'provider' => 'notion',
        'access_token' => 'ntn_test_token',
    ]);

    $fakeResponse = [
        'results' => [
            [
                'id' => 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
                'url' => 'https://notion.so/db',
            ],
        ],
    ];

    Http::fake([
        'https://api.notion.com/v1/search' => Http::response($fakeResponse, 200),
    ]);

    $client = new NotionApiClient($provider);
    $databases = $client->listDatabases();

    expect($databases[0]['title'])->toBe('');
    expect($databases[0]['icon'])->toBeNull();
});

it('returns database properties in expected format', function () {
    $user = $this->createUser();
    $provider = OAuthProvider::factory()->for($user)->create([
        'provider' => 'notion',
        'access_token' => 'ntn_test_token',
    ]);

    $dbId = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
    $fakeResponse = [
        'id' => $dbId,
        'properties' => [
            'Name' => ['id' => 'prop1', 'type' => 'title', 'name' => 'Name'],
            'Email' => ['id' => 'prop2', 'type' => 'email', 'name' => 'Email'],
            'Count' => ['id' => 'prop3', 'type' => 'number', 'name' => 'Count'],
        ],
    ];

    Http::fake([
        "https://api.notion.com/v1/databases/{$dbId}" => Http::response($fakeResponse, 200),
    ]);

    $client = new NotionApiClient($provider);
    $properties = $client->getDatabaseProperties($dbId);

    expect($properties)->toHaveCount(3);
    expect($properties[0])->toBe([
        'name' => 'Name',
        'type' => 'title',
        'id' => 'prop1',
    ]);
    expect($properties[1]['name'])->toBe('Email');
    expect($properties[2]['type'])->toBe('number');
});

it('sends correct Authorization and Notion-Version headers', function () {
    $user = $this->createUser();
    $provider = OAuthProvider::factory()->for($user)->create([
        'provider' => 'notion',
        'access_token' => 'ntn_test_token_123',
    ]);

    Http::fake([
        'https://api.notion.com/v1/search' => Http::response(['results' => []], 200),
    ]);

    $client = new NotionApiClient($provider);
    $client->listDatabases();

    Http::assertSent(function ($request) {
        return $request->hasHeader('Authorization', 'Bearer ntn_test_token_123')
            && $request->hasHeader('Notion-Version', '2022-06-28');
    });
});

it('sends GET request for getDatabase', function () {
    $user = $this->createUser();
    $provider = OAuthProvider::factory()->for($user)->create([
        'provider' => 'notion',
        'access_token' => 'ntn_test_token',
    ]);

    $dbId = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';

    Http::fake([
        "https://api.notion.com/v1/databases/{$dbId}" => Http::response(['id' => $dbId], 200),
    ]);

    $client = new NotionApiClient($provider);
    $client->getDatabase($dbId);

    Http::assertSent(function ($request) use ($dbId) {
        return $request->url() === "https://api.notion.com/v1/databases/{$dbId}"
            && $request->method() === 'GET';
    });
});

it('sends POST request for listDatabases', function () {
    $user = $this->createUser();
    $provider = OAuthProvider::factory()->for($user)->create([
        'provider' => 'notion',
        'access_token' => 'ntn_test_token',
    ]);

    Http::fake([
        'https://api.notion.com/v1/search' => Http::response(['results' => []], 200),
    ]);

    $client = new NotionApiClient($provider);
    $client->listDatabases();

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.notion.com/v1/search'
            && $request->method() === 'POST'
            && $request->data()['filter'] === ['value' => 'database', 'property' => 'object']
            && $request->data()['page_size'] === 100;
    });
});
