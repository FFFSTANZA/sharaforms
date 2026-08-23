<?php

namespace App\Integrations\OAuth\Drivers;

use App\Integrations\OAuth\Drivers\Contracts\OAuthDriver;
use Laravel\Socialite\Contracts\User;
use Laravel\Socialite\Facades\Socialite;

class OAuthNotionDriver implements OAuthDriver
{
    private ?string $redirectUrl = null;
    private ?array $scopes = [];
    private ?string $state = null;

    protected $provider;

    public function __construct()
    {
        $this->provider = Socialite::driver('notion');
    }

    public function getRedirectUrl(): string
    {
        $parameters = [];

        if ($this->state) {
            $parameters['state'] = $this->state;
        }

        return $this->provider
            ->scopes($this->scopes ?? [])
            ->stateless()
            ->redirectUrl($this->redirectUrl ?? config('services.notion.redirect'))
            ->with($parameters)
            ->redirect()
            ->getTargetUrl();
    }

    public function getUser(): User
    {
        return $this->provider
            ->stateless()
            ->redirectUrl($this->redirectUrl ?? config('services.notion.redirect'))
            ->user();
    }

    public function canCreateUser(): bool
    {
        return false;
    }

    public function setRedirectUrl(string $url): self
    {
        $this->redirectUrl = $url;
        return $this;
    }

    public function setScopes(array $scopes): self
    {
        $this->scopes = $scopes;
        return $this;
    }

    public function setState(string $state): self
    {
        $this->state = $state;
        return $this;
    }

    public function getScopesForIntent(string $intent): array
    {
        return match ($intent) {
            default => [],
        };
    }
}
