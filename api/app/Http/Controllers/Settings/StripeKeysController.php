<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreStripeKeysRequest;
use App\Http\Resources\OAuthProviderResource;
use App\Models\OAuthProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Stores a form creator's own Stripe API keys ("bring your own keys").
 *
 * This lets creators collect payments without the platform having its own
 * Stripe account / Stripe Connect OAuth: PaymentIntents are created directly
 * on their account with their key, and card data is still tokenized by
 * Stripe.js client-side.
 *
 * Security invariants:
 * - The secret key is validated with Stripe before being stored.
 * - The secret key is stored encrypted (Crypt) and NEVER returned to the client.
 * - Restricted keys (rk_live_...) are recommended; full secrets are accepted.
 */
class StripeKeysController extends Controller
{
    private const STRIPE_API_BASE = 'https://api.stripe.com';

    public function store(StoreStripeKeysRequest $request)
    {
        if (config('app.self_hosted')) {
            return $this->error(['message' => 'Payment features are not available in the self-hosted version.'], 403);
        }

        if (!config('services.stripe.own_keys_enabled')) {
            return $this->error(['message' => 'Connecting Stripe via your own API keys is currently disabled.'], 403);
        }

        // Inputs are trimmed in StoreStripeKeysRequest::prepareForValidation().
        $publishableKey = $request->input('publishable_key');
        $secretKey = $request->input('secret_key');

        $verification = $this->verifyWithStripe($secretKey);
        if ($verification === null) {
            return $this->error(['message' => 'Stripe rejected this API key. Please double-check the key and try again.'], 422);
        }

        [$accountId, $displayName] = $verification;

        // One API-key connection per user: update on re-save, create on first.
        /** @var OAuthProvider $provider */
        $provider = OAuthProvider::firstOrNew([
            'user_id' => Auth::id(),
            'provider' => \App\Integrations\OAuth\OAuthProviderService::StripeOwnKeys->value,
        ]);

        $provider->fill([
            // Real acct_... id when readable; stable pseudo-id derived from the
            // key otherwise (restricted keys may lack Account read permission).
            'provider_user_id' => $accountId,
            // Encrypted at rest; hidden from all serialization via $hidden.
            'access_token' => Crypt::encryptString($secretKey),
            'publishable_key' => $publishableKey,
            'name' => $displayName,
            // Legacy columns are NOT NULL in existing databases; own-keys
            // connections have neither refresh tokens nor OAuth scopes.
            'refresh_token' => $provider->refresh_token ?: '',
            'scopes' => $provider->scopes ?? [],
        ])->save();

        Log::info('Stripe own-keys connection saved', [
            'user_id' => Auth::id(),
            'provider_id' => $provider->id,
            'stripe_account' => $accountId,
        ]);

        return $this->success([
            'message' => 'Stripe account connected.',
            'provider' => OAuthProviderResource::make($provider),
        ]);
    }

    /**
     * Verify the secret key with Stripe and derive [account_id, display_name].
     *
     * Strategy:
     * 1. GET /v1/account works for full secret keys and restricted keys with
     *    "Account" read permission. Gives us the real account id + name.
     * 2. If that fails due to permissions (common for minimal restricted keys),
     *    probe with a tiny unconfirmed PaymentIntent create + immediate cancel.
     *    That validates exactly the permission needed to charge.
     */
    private function verifyWithStripe(string $secretKey): ?array
    {
        try {
            $response = Http::withToken($secretKey)
                ->timeout(10)
                ->get(self::STRIPE_API_BASE.'/v1/account');
        } catch (\Throwable $e) {
            Log::warning('Stripe own-keys verification request failed', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        if ($response->successful()) {
            $account = $response->json();
            $accountId = $account['id'] ?? null;

            // Defensive: a 200 without an id must never reach the NOT NULL
            // provider_user_id column.
            if (empty($accountId)) {
                Log::warning('Stripe own-keys account response missing id');

                return null;
            }

            return [
                $accountId,
                $account['business_profile']['name']
                    ?? $account['settings']['dashboard']['display_name']
                    ?? $account['email']
                    ?? 'Stripe account',
            ];
        }

        $errorCode = $response->json('error.code');

        if ($response->status() === 401 || $errorCode === 'invalid_api_key') {
            return null;
        }

        // Restricted key without Account read permission: probe charge permission.
        if ($response->status() === 403 || $errorCode === 'insufficient_permission' || $errorCode === 'permissions_not_satisfied') {
            return $this->probeChargePermission($secretKey);
        }

        Log::warning('Stripe own-keys verification failed unexpectedly', [
            'status' => $response->status(),
            'error_code' => $errorCode,
        ]);

        return null;
    }

    /**
     * Create a 50-cent unconfirmed PaymentIntent and cancel it immediately to
     * prove the key can create charges. No money moves either way.
     */
    private function probeChargePermission(string $secretKey): ?array
    {
        try {
            $create = Http::asForm()
                ->withToken($secretKey)
                ->timeout(10)
                ->post(self::STRIPE_API_BASE.'/v1/payment_intents', [
                    'amount' => 50,
                    'currency' => 'usd',
                    'metadata[sharaforms_probe]' => '1',
                ]);
        } catch (\Throwable $e) {
            Log::warning('Stripe own-keys charge probe request failed', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        if (!$create->successful()) {
            Log::info('Stripe own-keys charge probe failed', [
                'status' => $create->status(),
                'error_code' => $create->json('error.code'),
            ]);

            return null;
        }

        $intentId = $create->json('id');

        if ($intentId) {
            try {
                Http::asForm()
                    ->withToken($secretKey)
                    ->timeout(10)
                    ->post(self::STRIPE_API_BASE."/v1/payment_intents/{$intentId}/cancel");
            } catch (\Throwable) {
                // Best-effort cleanup; the unconfirmed intent expires on its own.
            }
        }

        $keyFingerprint = substr(hash('sha256', $secretKey), 0, 16);

        return ['acct_own_'.$keyFingerprint, 'Stripe API key'];
    }
}
