<?php

use App\Service\OAuth\OAuthContextService;
use Illuminate\Support\Facades\Cache;
use Tests\TestHelpers\MockOAuthProviders;

uses()->group('oauth', 'security');

beforeEach(function () {
    MockOAuthProviders::mockGoogleProvider();
});

describe('OAuth connect issues a state cookie', function () {
    it('sets the oauth_state cookie when initiating a flow', function () {
        $response = $this->postJson('/oauth/connect/google', [
            'intent' => 'auth',
            'utm_data' => ['source' => 'test'],
        ]);

        $response->assertSuccessful();
        $response->assertCookie(OAuthContextService::STATE_COOKIE_NAME);
    });

    it('ties the cookie value to the generated state token', function () {
        $response = $this->postJson('/oauth/connect/google', [
            'intent' => 'auth',
        ]);

        $state = $response->json('state');
        $response->assertCookie(OAuthContextService::STATE_COOKIE_NAME, $state);
    });
});

describe('OAuth callback validates state before the code exchange', function () {
    it('rejects a callback with no state parameter', function () {
        $response = $this->postJson('/oauth/google/callback', [
            'code' => 'code_123',
        ]);

        $response->assertStatus(419);
    });

    it('rejects a callback with an unknown state token', function () {
        $response = $this->postJson('/oauth/google/callback', [
            'state' => 'bogus_state_token',
            'code' => 'code_123',
        ]);

        $response->assertStatus(419);
    });

    it('rejects a callback with a valid context but no oauth_state cookie', function () {
        $state = startFlow();

        $response = $this->postJson('/oauth/google/callback', [
            'state' => $state,
            'code' => 'code_123',
        ]);

        $response->assertStatus(419);
    });

    it('rejects a callback when the oauth_state cookie does not match the state token', function () {
        $state = startFlow();

        $response = $this->withCredentials()
            ->withCookie(OAuthContextService::STATE_COOKIE_NAME, 'some_other_state')
            ->postJson('/oauth/google/callback', [
                'state' => $state,
                'code' => 'code_123',
            ]);

        $response->assertStatus(419);
    });

    it('rejects a callback that omits the authorization code', function () {
        $state = startFlow();

        $response = $this->withCredentials()
            ->withCookie(OAuthContextService::STATE_COOKIE_NAME, $state)
            ->postJson('/oauth/google/callback', [
                'state' => $state,
            ]);

        $response->assertStatus(400);
        $response->assertJson(['error' => 'Missing authorization code']);
    });
});

function startFlow(): string
{
    return test()->postJson('/oauth/connect/google', ['intent' => 'auth'])->json('state');
}

afterEach(function () {
    Cache::flush();
    MockOAuthProviders::cleanup();
});