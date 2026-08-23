<?php

namespace App\Integrations\OAuth\Drivers\Notion;

use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class Provider extends AbstractProvider
{
    public const IDENTIFIER = 'NOTION';

    protected $scopes = [];

    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase(
            'https://api.notion.com/v1/oauth/authorize',
            $state
        );
    }

    protected function getTokenUrl()
    {
        return 'https://api.notion.com/v1/oauth/token';
    }

    protected function getUserByToken($token)
    {
        // Notion has no /me endpoint. User info is in the token response itself.
        // Return the full token response so mapUserToObject receives usable data.
        return $this->credentialsResponseBody ?? [];
    }

    protected function mapUserToObject(array $user)
    {
        // Notion token response shape:
        // { bot_id, workspace_name, workspace_icon, workspace_id,
        //   owner: { type: "user", user: { email: "...", ... } } }
        $email = $user['owner']['user']['email']
            ?? $user['owner_email']   // fallback if shape varies
            ?? null;

        return (new User())->setRaw($user)->map([
            'id'       => $user['bot_id'] ?? $user['workspace_id'] ?? null,
            'nickname' => $user['workspace_name'] ?? null,
            'name'     => $user['workspace_name'] ?? null,
            'email'    => $email,
            'avatar'   => $user['workspace_icon'] ?? null,
        ]);
    }

    /**
     * Notion requires Basic auth (client_id:client_secret) for token exchange.
     * Credentials must NOT appear in the POST body — only in the Authorization header.
     */
    protected function getTokenFields($code)
    {
        return [
            'grant_type'   => 'authorization_code',
            'code'         => $code,
            'redirect_uri' => $this->redirectUrl,
        ];
    }

    /**
     * Add Basic auth header for Notion token exchange.
     */
    protected function getTokenHeaders($code)
    {
        return [
            'Accept'       => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret),
        ];
    }

    /**
     * Override token exchange to send Basic auth instead of body credentials.
     * Notion rejects requests that include client_id/client_secret in the POST body.
     */
    public function getAccessTokenResponse($code)
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'headers' => $this->getTokenHeaders($code),
            'form_params' => $this->getTokenFields($code),
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * Notion requires Basic auth for refresh tokens too, with no body credentials.
     */
    protected function getRefreshTokenResponse($refreshToken)
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'headers' => [
                'Accept'        => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret),
            ],
            'form_params' => [
                'grant_type'    => 'refresh_token',
                'refresh_token' => $refreshToken,
            ],
        ]);

        return json_decode($response->getBody(), true);
    }
}
