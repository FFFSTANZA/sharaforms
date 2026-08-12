<?php

use App\Service\OAuth\OAuthContextService;
use Illuminate\Http\Request;

uses()->group('oauth', 'security');

beforeEach(function () {
    // Bind a fresh request so request()->cookie() reads deterministic state.
    app()->instance('request', Request::create('/'));
});

describe('OAuthContextService state cookie', function () {
    it('issues a SameSite=Lax HttpOnly cookie carrying the state token', function () {
        $cookie = (new OAuthContextService())->issueStateCookie('abc123');

        expect($cookie->getName())->toBe(OAuthContextService::STATE_COOKIE_NAME);
        expect($cookie->getValue())->toBe('abc123');
        expect($cookie->getDomain())->toBeNull();
        expect($cookie->isHttpOnly())->toBeTrue();
        expect($cookie->getSameSite())->toBe('lax');
    });

    it('matches when the oauth_state cookie equals the state token', function () {
        request()->cookies->set(OAuthContextService::STATE_COOKIE_NAME, 'abc123');

        $service = new OAuthContextService();
        expect($service->stateCookieMatches('abc123'))->toBeTrue();
    });

    it('rejects when the cookie value differs from the state token', function () {
        request()->cookies->set(OAuthContextService::STATE_COOKIE_NAME, 'different');

        $service = new OAuthContextService();
        expect($service->stateCookieMatches('abc123'))->toBeFalse();
    });

    it('rejects when the oauth_state cookie is absent', function () {
        $service = new OAuthContextService();
        expect($service->stateCookieMatches('abc123'))->toBeFalse();
    });

    it('rejects empty cookie values', function () {
        request()->cookies->set(OAuthContextService::STATE_COOKIE_NAME, '');

        $service = new OAuthContextService();
        expect($service->stateCookieMatches('abc123'))->toBeFalse();
    });
});