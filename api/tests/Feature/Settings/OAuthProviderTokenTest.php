<?php

use App\Integrations\OAuth\OAuthProviderService;
use App\Models\OAuthProvider;

describe('OAuth provider access token endpoint', function () {
    it('returns 401 for guests', function () {
        $owner = $this->createUser();
        $provider = OAuthProvider::factory()->create(['user_id' => $owner->id]);

        $this->getJson(route('settings.providers.token', $provider))
            ->assertUnauthorized();
    });

    it('returns 403 for a provider owned by another user', function () {
        $owner = $this->createUser();
        $provider = OAuthProvider::factory()->create(['user_id' => $owner->id]);

        $this->actingAsUser();

        $this->getJson(route('settings.providers.token', $provider))
            ->assertForbidden();
    });

    it('returns the current access token for an owned Google provider', function () {
        $user = $this->actingAsUser();
        $provider = OAuthProvider::factory()->create([
            'user_id' => $user->id,
            'access_token' => 'valid-google-token',
        ]);

        $this->getJson(route('settings.providers.token', $provider))
            ->assertOk()
            ->assertJson([
                'access_token' => 'valid-google-token',
                'expires_in' => 3600,
            ]);
    });

    it('returns 403 for a non-Google provider', function () {
        $user = $this->actingAsUser();
        $provider = OAuthProvider::factory()->create([
            'user_id' => $user->id,
            'provider' => OAuthProviderService::Stripe->value,
        ]);

        $this->getJson(route('settings.providers.token', $provider))
            ->assertForbidden()
            ->assertJson(['message' => 'This provider cannot issue an access token.']);
    });

    it('returns 422 when the Google token cannot be refreshed', function () {
        $user = $this->actingAsUser();
        $provider = OAuthProvider::factory()->create([
            'user_id' => $user->id,
            'access_token' => '',
            'refresh_token' => '',
        ]);

        $this->getJson(route('settings.providers.token', $provider))
            ->assertStatus(422);
    });
});