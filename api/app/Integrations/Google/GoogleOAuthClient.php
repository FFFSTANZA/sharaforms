<?php

namespace App\Integrations\Google;

use App\Models\OAuthProvider;
use Google\Client as Client;

class GoogleOAuthClient
{
    protected Client $client;

    public function __construct(
        protected OAuthProvider $provider
    ) {
        $this->client = new Client();
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
        $this->client->setAccessToken([
            'access_token' => $this->provider->access_token,
            'created' => $this->provider->updated_at->getTimestamp(),
            'expires_in' => 3600,
        ]);
    }

    public function getClient(): Client
    {
        if ($this->client->isAccessTokenExpired()) {
            $this->refreshToken();
        }

        return $this->client;
    }

    public function refreshToken(): static
    {
        if (! $this->provider->refresh_token) {
            throw new GoogleOAuthException(
                'Google account is missing a refresh token. Reconnect the account to grant offline access.',
                GoogleOAuthException::MISSING_REFRESH_TOKEN
            );
        }

        try {
            $this->client->refreshToken($this->provider->refresh_token);
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            throw new GoogleOAuthException(
                'Could not reach Google to refresh the access token. Check your connection and try again.',
                GoogleOAuthException::NETWORK_ERROR,
                $e
            );
        } catch (\Throwable $e) {
            throw new GoogleOAuthException(
                'Google rejected the refresh token. Reconnect the account to renew access.',
                GoogleOAuthException::INVALID_GRANT,
                $e
            );
        }

        $token = $this->client->getAccessToken();
        if (! is_array($token) || empty($token['access_token'])) {
            throw new GoogleOAuthException(
                'Google did not return a usable access token. Reconnect the account to renew access.',
                GoogleOAuthException::INVALID_GRANT
            );
        }

        $updateData = ['access_token' => $token['access_token']];

        if (isset($token['refresh_token'])) {
            $updateData['refresh_token'] = $token['refresh_token'];
        }

        $this->provider->update($updateData);

        return $this;
    }

    /**
     * Returns a usable access token, refreshing first when the Google client considers it expired.
     *
     * @throws \RuntimeException when no access token is available after refresh
     */
    public function getAccessTokenString(): string
    {
        $client = $this->getClient();
        $token = $client->getAccessToken();

        if (! is_array($token) || empty($token['access_token'])) {
            throw new \RuntimeException('Missing Google access token.');
        }

        if ($client->isAccessTokenExpired()) {
            throw new \RuntimeException('Google access token expired and could not be refreshed.');
        }

        return $token['access_token'];
    }
}
