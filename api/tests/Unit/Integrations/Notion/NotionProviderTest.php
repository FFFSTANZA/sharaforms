<?php

use App\Integrations\OAuth\Drivers\Notion\Provider;

uses(\Tests\TestCase::class);

it('returns the Notion identifier', function () {
    expect(Provider::IDENTIFIER)->toBe('NOTION');
});

it('sets empty scopes by default', function () {
    $provider = new Provider(
        $this->app['request'],
        'test-client-id',
        'test-client-secret',
        'https://example.com/callback',
        []
    );

    // Access the scopes via reflection since it's protected
    $reflection = new ReflectionProperty(Provider::class, 'scopes');
    $reflection->setAccessible(true);

    expect($reflection->getValue($provider))->toBe([]);
});

it('returns the correct auth URL base', function () {
    $provider = new Provider(
        $this->app['request'],
        'test-client-id',
        'test-client-secret',
        'https://example.com/callback',
        []
    );

    $reflection = new ReflectionMethod(Provider::class, 'getAuthUrl');
    $reflection->setAccessible(true);

    $result = $reflection->invoke($provider, 'test-state');

    expect($result)->toContain('https://api.notion.com/v1/oauth/authorize');
});

it('returns the correct token URL', function () {
    $provider = new Provider(
        $this->app['request'],
        'test-client-id',
        'test-client-secret',
        'https://example.com/callback',
        []
    );

    $reflection = new ReflectionMethod(Provider::class, 'getTokenUrl');
    $reflection->setAccessible(true);

    $result = $reflection->invoke($provider);

    expect($result)->toBe('https://api.notion.com/v1/oauth/token');
});

it('returns credentialsResponseBody from getUserByToken', function () {
    $provider = new Provider(
        $this->app['request'],
        'test-client-id',
        'test-client-secret',
        'https://example.com/callback',
        []
    );

    // Set credentialsResponseBody via reflection
    $reflection = new ReflectionProperty(get_class($provider), 'credentialsResponseBody');
    $reflection->setAccessible(true);
    $reflection->setValue($provider, [
        'bot_id' => 'bot_123',
        'workspace_name' => 'Test Workspace',
        'workspace_id' => 'ws_456',
        'owner' => [
            'type' => 'user',
            'user' => ['email' => 'user@example.com'],
        ],
    ]);

    $getUserByToken = new ReflectionMethod(Provider::class, 'getUserByToken');
    $getUserByToken->setAccessible(true);

    $result = $getUserByToken->invoke($provider, 'any-token');

    expect($result)->toHaveKey('bot_id');
    expect($result['bot_id'])->toBe('bot_123');
    expect($result['workspace_name'])->toBe('Test Workspace');
});

it('returns empty array when credentialsResponseBody is null', function () {
    $provider = new Provider(
        $this->app['request'],
        'test-client-id',
        'test-client-secret',
        'https://example.com/callback',
        []
    );

    // credentialsResponseBody is null by default
    $getUserByToken = new ReflectionMethod(Provider::class, 'getUserByToken');
    $getUserByToken->setAccessible(true);

    $result = $getUserByToken->invoke($provider, 'any-token');

    expect($result)->toBe([]);
});

it('maps user object from Notion token response', function () {
    $provider = new Provider(
        $this->app['request'],
        'test-client-id',
        'test-client-secret',
        'https://example.com/callback',
        []
    );

    $mapUserToObject = new ReflectionMethod(Provider::class, 'mapUserToObject');
    $mapUserToObject->setAccessible(true);

    $notionUser = [
        'bot_id' => 'bot_abc',
        'workspace_name' => 'Acme Corp',
        'workspace_icon' => '🏢',
        'workspace_id' => 'ws_xyz',
        'owner' => [
            'type' => 'user',
            'user' => ['email' => 'admin@acme.com', 'name' => 'Admin'],
        ],
    ];

    /** @var \SocialiteProviders\Manager\OAuth2\User $user */
    $user = $mapUserToObject->invoke($provider, $notionUser);

    expect($user->getId())->toBe('bot_abc');
    expect($user->getNickname())->toBe('Acme Corp');
    expect($user->getName())->toBe('Acme Corp');
    expect($user->getEmail())->toBe('admin@acme.com');
    expect($user->getAvatar())->toBe('🏢');
});

it('falls back to workspace_id when bot_id is missing', function () {
    $provider = new Provider(
        $this->app['request'],
        'test-client-id',
        'test-client-secret',
        'https://example.com/callback',
        []
    );

    $mapUserToObject = new ReflectionMethod(Provider::class, 'mapUserToObject');
    $mapUserToObject->setAccessible(true);

    $notionUser = [
        'workspace_id' => 'ws_fallback',
        'workspace_name' => 'Fallback Corp',
        'owner' => [
            'type' => 'user',
            'user' => ['email' => 'user@test.com'],
        ],
    ];

    $user = $mapUserToObject->invoke($provider, $notionUser);

    expect($user->getId())->toBe('ws_fallback');
});

it('falls back to owner_email when owner.user.email is missing', function () {
    $provider = new Provider(
        $this->app['request'],
        'test-client-id',
        'test-client-secret',
        'https://example.com/callback',
        []
    );

    $mapUserToObject = new ReflectionMethod(Provider::class, 'mapUserToObject');
    $mapUserToObject->setAccessible(true);

    $notionUser = [
        'bot_id' => 'bot_123',
        'workspace_name' => 'Test',
        'owner' => [
            'type' => 'user',
            'user' => [],
        ],
        'owner_email' => 'fallback@test.com',
    ];

    $user = $mapUserToObject->invoke($provider, $notionUser);

    expect($user->getEmail())->toBe('fallback@test.com');
});

it('returns null email when neither owner.user.email nor owner_email exist', function () {
    $provider = new Provider(
        $this->app['request'],
        'test-client-id',
        'test-client-secret',
        'https://example.com/callback',
        []
    );

    $mapUserToObject = new ReflectionMethod(Provider::class, 'mapUserToObject');
    $mapUserToObject->setAccessible(true);

    $notionUser = [
        'bot_id' => 'bot_123',
        'workspace_name' => 'Test',
        'owner' => [
            'type' => 'user',
            'user' => [],
        ],
    ];

    $user = $mapUserToObject->invoke($provider, $notionUser);

    expect($user->getEmail())->toBeNull();
});

it('returns empty token fields without client_id or client_secret in body', function () {
    $provider = new Provider(
        $this->app['request'],
        'test-client-id',
        'test-client-secret',
        'https://example.com/callback',
        []
    );

    $getTokenFields = new ReflectionMethod(Provider::class, 'getTokenFields');
    $getTokenFields->setAccessible(true);

    $fields = $getTokenFields->invoke($provider, 'auth_code_123');

    expect($fields)->toBe([
        'grant_type' => 'authorization_code',
        'code' => 'auth_code_123',
        'redirect_uri' => 'https://example.com/callback',
    ]);

    // Must NOT contain client_id or client_secret
    expect($fields)->not->toHaveKey('client_id');
    expect($fields)->not->toHaveKey('client_secret');
});

it('returns Basic auth header for token exchange', function () {
    $provider = new Provider(
        $this->app['request'],
        'my-client-id',
        'my-client-secret',
        'https://example.com/callback',
        []
    );

    $getTokenHeaders = new ReflectionMethod(Provider::class, 'getTokenHeaders');
    $getTokenHeaders->setAccessible(true);

    $headers = $getTokenHeaders->invoke($provider, 'code123');

    expect($headers)->toHaveKey('Authorization');
    expect($headers['Authorization'])->toStartWith('Basic ');
    expect($headers['Authorization'])->toBe('Basic ' . base64_encode('my-client-id:my-client-secret'));
    expect($headers)->toHaveKey('Accept');
    expect($headers['Accept'])->toBe('application/json');
});

it('stores the raw token response in the user object', function () {
    $provider = new Provider(
        $this->app['request'],
        'test-client-id',
        'test-client-secret',
        'https://example.com/callback',
        []
    );

    $mapUserToObject = new ReflectionMethod(Provider::class, 'mapUserToObject');
    $mapUserToObject->setAccessible(true);

    $rawResponse = [
        'bot_id' => 'bot_123',
        'workspace_name' => 'Test',
        'workspace_icon' => '📋',
        'workspace_id' => 'ws_456',
        'owner' => [
            'type' => 'user',
            'user' => ['email' => 'test@test.com'],
        ],
    ];

    $user = $mapUserToObject->invoke($provider, $rawResponse);

    expect($user->getRaw())->toBe($rawResponse);
});
