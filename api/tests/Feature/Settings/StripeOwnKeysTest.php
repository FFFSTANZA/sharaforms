<?php

use App\Integrations\OAuth\OAuthProviderService;
use App\Models\OAuthProvider;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

function stripeKeysEndpoint(): string
{
    return route('settings.providers.stripe-keys.store');
}

describe('Stripe own API keys connection', function () {
    it('requires authentication', function () {
        $this->postJson(stripeKeysEndpoint(), [
            'publishable_key' => 'pk_live_abc123',
            'secret_key' => 'rk_live_abc123def456ghi789',
        ])->assertUnauthorized();
    });

    it('is blocked when the own-keys feature is disabled', function () {
        config(['services.stripe.own_keys_enabled' => false]);

        Http::fake(['https://api.stripe.com/*' => Http::response([], 500)]);

        $this->actingAsUser();

        $this->postJson(stripeKeysEndpoint(), [
            'publishable_key' => 'pk_live_abc123',
            'secret_key' => 'rk_live_abc123def456ghi789',
        ])->assertForbidden();
    });

    it('is blocked on self-hosted instances', function () {
        config(['app.self_hosted' => true]);

        Http::fake(['https://api.stripe.com/*' => Http::response([], 500)]);

        $this->actingAsUser();

        $this->postJson(stripeKeysEndpoint(), [
            'publishable_key' => 'pk_live_abc123',
            'secret_key' => 'rk_live_abc123def456ghi789',
        ])->assertForbidden();
    });

    it('rejects malformed keys without creating a row', function () {
        $user = $this->actingAsUser();

        $this->postJson(stripeKeysEndpoint(), [
            'publishable_key' => 'not-a-stripe-key',
            'secret_key' => 'rk_live_abc123def456ghi789',
        ])->assertStatus(422);

        $this->postJson(stripeKeysEndpoint(), [
            'publishable_key' => 'pk_live_abc123',
            'secret_key' => 'sk_wrong_prefix_abc123',
        ])->assertStatus(422);

        expect(OAuthProvider::where('user_id', $user->id)
            ->where('provider', OAuthProviderService::StripeOwnKeys->value)->count())->toBe(0);
    });

    it('returns 422 when Stripe rejects the key and stores nothing', function () {
        $user = $this->actingAsUser();

        Http::fake([
            'https://api.stripe.com/v1/account' => Http::response([
                'error' => ['code' => 'invalid_api_key', 'message' => 'Invalid API Key'],
            ], 401),
        ]);

        $this->postJson(stripeKeysEndpoint(), [
            'publishable_key' => 'pk_test_abc123',
            'secret_key' => 'rk_test_abc123def456ghi789',
        ])->assertStatus(422);

        expect(OAuthProvider::where('user_id', $user->id)->count())->toBe(0);
    });

    it('stores an encrypted connection when the account is readable', function () {
        $user = $this->actingAsUser();
        $secret = 'sk_test_abc123def456ghi789jkl';

        Http::fake([
            'https://api.stripe.com/v1/account' => Http::response([
                'id' => 'acct_1NiseIsXZO5x',
                'business_profile' => ['name' => 'Acme Events'],
                'settings' => ['dashboard' => ['display_name' => 'Acme']],
            ], 200),
        ]);

        $this->postJson(stripeKeysEndpoint(), [
            'publishable_key' => 'pk_test_abc123',
            'secret_key' => $secret,
        ])
            ->assertOk()
            ->assertJsonPath('type', 'success');

        /** @var OAuthProvider $provider */
        $provider = OAuthProvider::where('user_id', $user->id)
            ->where('provider', OAuthProviderService::StripeOwnKeys->value)
            ->firstOrFail();

        expect($provider->provider_user_id)->toBe('acct_1NiseIsXZO5x')
            ->and($provider->publishable_key)->toBe('pk_test_abc123')
            ->and($provider->name)->toBe('Acme Events')
            // Encrypted at rest, never stored in plaintext
            ->and($provider->access_token)->not->toBe($secret)
            ->and(Crypt::decryptString($provider->access_token))->toBe($secret);
    });

    it('never returns the secret key in the response', function () {
        $user = $this->actingAsUser();
        $secret = 'sk_test_abc123def456ghi789jkl';

        Http::fake([
            'https://api.stripe.com/v1/account' => Http::response(['id' => 'acct_secretcheck'], 200),
        ]);

        $response = $this->postJson(stripeKeysEndpoint(), [
            'publishable_key' => 'pk_test_abc123',
            'secret_key' => $secret,
        ])->assertOk();

        expect($response->json())->not->toContain($secret);
    });

    it('falls back to a charge probe for restricted keys without account read access', function () {
        $user = $this->actingAsUser();
        $secret = 'rk_test_chargesonly123456';

        Http::fake([
            'https://api.stripe.com/v1/account' => Http::response([
                'error' => ['code' => 'insufficient_permission'],
            ], 403),
            'https://api.stripe.com/v1/payment_intents' => Http::response([
                'id' => 'pi_probe_123',
                'object' => 'payment_intent',
            ], 200),
            'https://api.stripe.com/v1/payment_intents/pi_probe_123/cancel' => Http::response([
                'id' => 'pi_probe_123',
                'status' => 'canceled',
            ], 200),
        ]);

        $this->postJson(stripeKeysEndpoint(), [
            'publishable_key' => 'pk_test_abc123',
            'secret_key' => $secret,
        ])->assertOk();

        /** @var OAuthProvider $provider */
        $provider = OAuthProvider::where('user_id', $user->id)
            ->where('provider', OAuthProviderService::StripeOwnKeys->value)
            ->firstOrFail();

        expect($provider->provider_user_id)->toStartWith('acct_own_')
            ->and($provider->name)->toBe('Stripe API key');
    });

    it('updates the existing connection instead of duplicating it', function () {
        $user = $this->actingAsUser();

        Http::fake([
            'https://api.stripe.com/v1/account' => Http::sequence()
                ->push(['id' => 'acct_first', 'business_profile' => ['name' => 'First']], 200)
                ->push(['id' => 'acct_second', 'business_profile' => ['name' => 'Second']], 200),
        ]);

        $this->postJson(stripeKeysEndpoint(), [
            'publishable_key' => 'pk_test_abc123',
            'secret_key' => 'sk_test_abc123def456ghi789jkl',
        ])->assertOk();

        $this->postJson(stripeKeysEndpoint(), [
            'publishable_key' => 'pk_test_def456',
            'secret_key' => 'sk_test_def456ghi789jklmno',
        ])->assertOk();

        $rows = OAuthProvider::where('user_id', $user->id)
            ->where('provider', OAuthProviderService::StripeOwnKeys->value)->get();

        expect($rows)->toHaveCount(1)
            ->and($rows->first()->provider_user_id)->toBe('acct_second')
            ->and($rows->first()->publishable_key)->toBe('pk_test_def456');
    });
});
