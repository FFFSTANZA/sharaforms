<?php

namespace App\Service\Telemetry;

use App\Enums\SettingsKey;
use App\Models\Setting;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

class TelemetryService
{
    public function __construct()
    {
    }

    public function shouldSendTelemetry(): bool
    {
        if (!config('telemetry.enabled', true)) {
            return false;
        }

        $isProduction = App::isProduction();

        return false;
    }

    public function getInstanceId(): ?string
    {
        return Cache::rememberForever('telemetry.instance_id', function () {
            return Setting::get(SettingsKey::INSTANCE_ID);
        });
    }

    public function getEndpoint(): string
    {
        return config('telemetry.endpoint');
    }

    public function getClientId(): ?string
    {
        return config('telemetry.client_id');
    }

    public function getClientSecret(): ?string
    {
        return config('telemetry.client_secret');
    }

    public function getAppVersion(): ?string
    {
        return config('app.docker_version');
    }

    public function getInstanceProperties(array $properties = []): array
    {
        return array_merge($properties, []);
    }

    public function createClient(): OpenPanelClient
    {
        return new OpenPanelClient(
            $this->getEndpoint(),
            $this->getClientId(),
            $this->getClientSecret()
        );
    }
}
