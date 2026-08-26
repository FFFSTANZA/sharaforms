<?php

use App\Integrations\OAuth\OAuthProviderService;
use App\Models\OAuthProvider;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->secret = 'rk_test_abc123def456ghi789jkl';

    $user = $this->actingAsUser();
    $workspace = $this->createUserWorkspace($user);

    $this->ownKeysProvider = OAuthProvider::factory()->for($user)->create([
        'provider' => OAuthProviderService::StripeOwnKeys->value,
        'provider_user_id' => 'acct_1NiseIsXZO5x',
        'publishable_key' => 'pk_test_creator123',
        'access_token' => Crypt::encryptString($this->secret),
    ]);

    // A second user with their own workspace, for cross-workspace checks
    $other = $this->createUser();
    $this->createUserWorkspace($other);
    $this->foreignProvider = OAuthProvider::factory()->for($other)->create([
        'provider' => OAuthProviderService::StripeOwnKeys->value,
        'provider_user_id' => 'acct_foreign',
        'publishable_key' => 'pk_test_foreign',
        'access_token' => Crypt::encryptString('rk_test_foreignkey123456'),
    ]);

    $this->form = $this->createForm($user, $workspace);
    $this->form->properties = array_merge($this->form->properties, [
        [
            'type' => 'payment',
            'stripe_account_id' => $this->ownKeysProvider->id,
            'amount' => 99.99,
            'currency' => 'USD',
        ],
    ]);
    $this->form->update();
});

it('returns the account publishable key in editor preview', function () {
    $response = $this->getJson(route('forms.stripe-connect.get-account', [
        'form' => $this->form->slug,
        'oauth_provider_id' => $this->ownKeysProvider->id,
    ]))
        ->assertSuccessful()
        ->assertJson([
            'type' => 'success',
            'mode' => 'own_keys',
            'publishableKey' => 'pk_test_creator123',
        ]);

    // The stored secret key must never leak through this endpoint.
    expect($response->json())->not->toContain($this->secret);
});

it('returns the account publishable key on the public saved form', function () {
    $this->getJson(route('forms.stripe-connect.get-account', $this->form->slug))
        ->assertSuccessful()
        ->assertJson([
            'type' => 'success',
            'mode' => 'own_keys',
            'publishableKey' => 'pk_test_creator123',
        ]);
});

it('rejects a payment-block account from outside the workspace', function () {
    // Still logged in as the form owner from beforeEach; the block now points
    // at an API-key connection that belongs to a user outside this workspace.
    $this->form->properties = array_map(function ($prop) {
        return $prop['type'] === 'payment'
            ? array_merge($prop, ['stripe_account_id' => $this->foreignProvider->id])
            : $prop;
    }, $this->form->properties);
    $this->form->update();

    $this->getJson(route('forms.stripe-connect.get-account', $this->form->slug))
        ->assertForbidden();
});

it('creates the payment intent directly on the creator account without a connected-account header', function () {
    Http::fake([
        'https://api.stripe.com/v1/payment_intents' => Http::response([
            'id' => 'pi_ownkeys_123',
            'object' => 'payment_intent',
            'client_secret' => 'pi_ownkeys_123_secret_xyz',
        ], 200),
    ]);

    $response = $this->postJson(route('forms.stripe-connect.create-intent', $this->form->slug), [
        'submission_data' => [],
    ])->assertSuccessful()
        ->assertJson([
            'type' => 'success',
            'intent' => [
                'id' => 'pi_ownkeys_123',
                'secret' => 'pi_ownkeys_123_secret_xyz',
            ],
        ]);

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.stripe.com/v1/payment_intents'
            && $request->header('Authorization') === ['Bearer '.$this->secret]
            // The key belongs to the target account: no Connect header allowed.
            && $request->header('Stripe-Account') === []
            && data_get($request->data(), 'amount') === 9999
            && data_get($request->data(), 'currency') === 'usd';
    });

    expect($response->json())->not->toContain($this->secret);
});

it('does not multiply zero-decimal currencies by 100', function () {
    $this->form->properties = array_map(function ($prop) {
        return $prop['type'] === 'payment'
            ? array_merge($prop, ['currency' => 'JPY'])
            : $prop;
    }, $this->form->properties);
    $this->form->update();

    Http::fake([
        'https://api.stripe.com/v1/payment_intents' => Http::response([
            'id' => 'pi_jpy_1',
            'client_secret' => 'pi_jpy_1_secret',
        ], 200),
    ]);

    $this->postJson(route('forms.stripe-connect.create-intent', $this->form->slug), [
        'submission_data' => [],
    ])->assertSuccessful();

    Http::assertSent(function ($request) {
        return data_get($request->data(), 'amount') === 100
            && data_get($request->data(), 'currency') === 'jpy';
    });
});

it('passes Stripe error messages through when the intent is rejected', function () {
    Http::fake([
        'https://api.stripe.com/v1/payment_intents' => Http::response([
            'error' => [
                'code' => 'currency_not_supported',
                'message' => 'This currency is not supported by your Stripe account.',
            ],
        ], 400),
    ]);

    $this->postJson(route('forms.stripe-connect.create-intent', $this->form->slug), [
        'submission_data' => [],
    ])
        ->assertStatus(400)
        ->assertJson([
            'type' => 'error',
            'message' => 'This currency is not supported by your Stripe account.',
        ]);
});

it('refuses own-keys payments when the feature is disabled', function () {
    config(['services.stripe.own_keys_enabled' => false]);

    $this->postJson(route('forms.stripe-connect.create-intent', $this->form->slug), [
        'submission_data' => [],
    ])->assertStatus(403);

    $this->getJson(route('forms.stripe-connect.get-account', $this->form->slug))
        ->assertStatus(403);
});
