<?php

namespace App\Http\Controllers\Content;

use App\Service\AI\AiProviderManager;
use App\Http\Controllers\Controller;

class FeatureFlagsController extends Controller
{
    public function index()
    {
        $featureFlags = [
            'self_hosted' => false,
            'setup_required' => false,
            'custom_domains' => config('custom-domains.enabled', false),
            'ai_features' => AiProviderManager::hasAvailableProvider(),
            'billing' => [
                'enabled' => !empty(config('cashier.key')) && !empty(config('cashier.secret')),
                'stripe_publishable_key' => config('cashier.key'),
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
                ],
                'telegram' => [
                    'bot_id' => $this->extractTelegramBotId(),
                ],
            ],
            'integrations' => [
                'zapier' => config('services.zapier.enabled'),
                'google_sheets' => !empty(config('services.google.client_id')) && !empty(config('services.google.client_secret')),
                'telegram' => !empty(config('services.telegram.bot_token')),
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
