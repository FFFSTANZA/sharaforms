<?php

namespace App\Http\Controllers\Content;

use App\Models\User;
use App\Service\AI\AiProviderManager;
use App\Http\Controllers\Controller;

class FeatureFlagsController extends Controller
{
    public function index()
    {
        $selfHosted = (bool) config('app.self_hosted', false);

        $featureFlags = [
            'self_hosted' => $selfHosted,
            'setup_required' => $selfHosted && !User::exists(),
            'custom_domains' => config('custom-domains.enabled', false),
            'ai_features' => AiProviderManager::hasAvailableProvider(),
            'billing' => [
                'enabled' => pricing_enabled(),
                'stripe_publishable_key' => config('cashier.key'),
                'stripe_own_keys_enabled' => (bool) config('services.stripe.own_keys_enabled', false),
            ],
            'storage' => [
                'local' => config('filesystems.default') === 'local',
                's3' => config('filesystems.default') !== 'local',
            ],
            'services' => [
                'unsplash' => !empty(config('services.unsplash.access_key')),
                'google' => [
                    'fonts' => !empty(config('services.google.fonts_api_key')),
                    'auth' => !empty(config('services.google.client_id')) && !empty(config('services.google.client_secret')),
                    'client_id' => config('services.google.client_id'),
                    'picker_api_key' => config('services.google.picker_api_key'),
                    'picker_app_id' => config('services.google.picker_app_id'),
                ],
                'notion' => [
                    'auth' => !empty(config('services.notion.client_id')) && !empty(config('services.notion.client_secret')),
                ],
                'telegram' => [
                    'bot_id' => $this->extractTelegramBotId(),
                ],
            ],
            'integrations' => [
                'zapier' => config('services.zapier.enabled'),
                'google_sheets' => !empty(config('services.google.client_id')) && !empty(config('services.google.client_secret')),
                'notion' => !empty(config('services.notion.client_id')) && !empty(config('services.notion.client_secret')),
                'telegram' => !empty(config('services.telegram.bot_token')),
                'microsoft_teams' => true,
                'google_chat' => true,
                'ntfy' => true,
                'airtable' => true,
                'trello' => true,
                'supabase' => true,
            ],
            'custom_code' => [
                'enable_self_hosted' => false,
            ],
            'oidc' => [
                'available' => $this->isOidcAvailable(),
                'forced' => config('oidc.force_login', false) && $this->isOidcAvailable(),
            ],
        ];

        return response()->json($featureFlags);
    }

    private function extractTelegramBotId(): ?string
    {
        $botToken = config('services.telegram.bot_token');
        if (!$botToken) {
            return null;
        }

        $parts = explode(':', $botToken);
        return $parts[0] ?? null;
    }

    private function isOidcAvailable(): bool
    {
        return !empty(config('services.oidc.client_id'))
            && !empty(config('services.oidc.client_secret'))
            && !empty(config('services.oidc.issuer'));
    }
}
