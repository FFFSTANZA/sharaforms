<?php

use App\Integrations\Google\GoogleOAuthClient;
use App\Integrations\Google\GoogleOAuthException;
use App\Models\OAuthProvider;

it('throws a missing-refresh-token exception with a reason when no refresh token exists', function () {
    $provider = OAuthProvider::factory()->make([
        'access_token' => 'ac_test',
        'refresh_token' => null,
        'created_at' => now()->subHour(),
        'updated_at' => now(),
    ]);

    $client = new GoogleOAuthClient($provider);

    try {
        $client->refreshToken();
        $this->fail('Expected GoogleOAuthException to be thrown.');
    } catch (GoogleOAuthException $e) {
        expect($e->reason())->toBe(GoogleOAuthException::MISSING_REFRESH_TOKEN);
        expect($e->getMessage())->toContain('missing a refresh token');
    }
});

it('uses distinct reason constants for each failure mode', function () {
    $reasons = [
        GoogleOAuthException::MISSING_REFRESH_TOKEN,
        GoogleOAuthException::NETWORK_ERROR,
        GoogleOAuthException::INVALID_GRANT,
    ];

    expect($reasons)->toHaveCount(3);
    expect(array_unique($reasons))->toHaveCount(3);
});

it('carries a previous exception for chained failures', function () {
    $previous = new RuntimeException('underlying failure');

    $e = new GoogleOAuthException('wrapped', GoogleOAuthException::NETWORK_ERROR, $previous);

    expect($e->getPrevious())->toBe($previous);
    expect($e->reason())->toBe(GoogleOAuthException::NETWORK_ERROR);
});