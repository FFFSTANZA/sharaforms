<?php

namespace App\Service\OAuth;

use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * OAuthContextService
 *
 * Manages OAuth flow context and metadata across authentication attempts.
 * Handles storage and retrieval of temporary data needed during OAuth callbacks:
 * - State tokens (for security in redirect-based flows)
 * - UTM tracking data (for user acquisition attribution)
 * - Invite tokens (for workspace invitations)
 * - Intent flags (auth vs integration)
 *
 * Uses cache for temporary storage with 5-minute TTL to prevent stale data.
 * Supports both redirect-based OAuth flows (with state tokens) and widget-based flows.
 */
class OAuthContextService
{
    private const CACHE_TTL_MINUTES = 5;
    private const REDIRECT_CONTEXT_PREFIX = 'oauth-context:state:';
    private const WIDGET_CONTEXT_PREFIX = 'oauth-context:widget:';

    /**
     * Name of the double-submit state cookie issued on /oauth/connect.
     * The cookie binds the initiated OAuth flow to the browser that started it,
     * preventing cross-site forged logins from reusing a leaked state token.
     */
    public const STATE_COOKIE_NAME = 'oauth_state';

    /**
     * Store OAuth context with a unique state token
     * Used for redirect-based OAuth flows (Google OAuth, GitHub, etc.)
     *
     * @param array $context {
     *     @type string $intent 'auth' or 'integration'
     *     @type array|null $utm_data UTM parameters for tracking
     *     @type string|null $invited_email Email address for invite validation
     *     @type string|null $invite_token Token for workspace invitations
     *     @type string|null $intention User's intention/purpose
     *     @type bool $autoClose Whether popup should auto-close
     * }
     * @return string State token for OAuth callback
     */
    public function storeContext(array $context): string
    {
        // Generate a unique state token for this OAuth flow
        $stateToken = bin2hex(random_bytes(16));
        $key = self::REDIRECT_CONTEXT_PREFIX . $stateToken;

        Cache::put($key, $context, now()->addMinutes(self::CACHE_TTL_MINUTES));

        return $stateToken;
    }

    /**
     * Store widget context with session-based key
     * Used for widget-based OAuth flows (Google One Tap, etc.)
     *
     * @param array $context OAuth context data
     * @return string Session ID key for retrieval
     */
    public function storeWidgetContext(array $context): string
    {
        $key = self::WIDGET_CONTEXT_PREFIX . session()->getId();
        Cache::put($key, $context, now()->addMinutes(self::CACHE_TTL_MINUTES));
        return $key;
    }

    /**
     * Get context from cache using state token
     */
    public function getContext(?string $stateToken = null): ?array
    {
        $stateToken = $stateToken ?? request()->input('state');

        if (!$stateToken) {
            return null;
        }

        $key = self::REDIRECT_CONTEXT_PREFIX . $stateToken;
        return Cache::get($key);
    }

    /**
     * Get widget context from cache using session ID
     */
    public function getWidgetContext(): ?array
    {
        $key = self::WIDGET_CONTEXT_PREFIX . session()->getId();
        return Cache::get($key);
    }

    /**
     * Clear context after use
     */
    public function clearContext(?string $stateToken = null): void
    {
        $stateToken = $stateToken ?? request()->input('state');

        if ($stateToken) {
            $key = self::REDIRECT_CONTEXT_PREFIX . $stateToken;
            Cache::forget($key);
        }
    }

    /**
     * Clear widget context after use
     */
    public function clearWidgetContext(): void
    {
        $key = self::WIDGET_CONTEXT_PREFIX . session()->getId();
        Cache::forget($key);
    }

    /**
     * Issue the double-submit state cookie for an OAuth flow.
     *
     * The cookie carries the same state token that is embedded in the
     * authorization URL. On callback, the token must match both the cached
     * context and this cookie (see stateCookieMatches) before the
     * authorization code is exchanged.
     */
    public function issueStateCookie(string $stateToken, int $minutes = 5): Cookie
    {
        return new Cookie(
            name: self::STATE_COOKIE_NAME,
            value: $stateToken,
            expire: now()->addMinutes($minutes)->getTimestamp(),
            path: '/',
            domain: null,
            secure: (bool) config('session.secure', false),
            httpOnly: true,
            raw: false,
            sameSite: (string) config('session.same_site', 'lax'),
        );
    }

    /**
     * Verify the oauth_state cookie matches the given state token.
     *
     * Uses hash_equals to prevent timing attacks. Returns false when the
     * cookie is absent, non-string, or does not match.
     */
    public function stateCookieMatches(string $stateToken): bool
    {
        $cookieValue = request()->cookie(self::STATE_COOKIE_NAME);

        return is_string($cookieValue)
            && $cookieValue !== ''
            && hash_equals($stateToken, $cookieValue);
    }

    /**
     * Determine intent from stored context
     */
    public function getIntent(): string
    {
        $context = $this->getContext();
        if (!isset($context['intent'])) {
            abort(419, 'OAuth context expired');
        }
        return $context['intent'];
    }

    /**
     * Get invited email from context
     */
    public function getInvitedEmail(): ?string
    {
        $context = $this->getContext();
        return $context['invited_email'] ?? null;
    }

    /**
     * Get invite token from context if present
     */
    public function getInviteToken(): ?string
    {
        $context = $this->getContext();
        return $context['invite_token'] ?? null;
    }

    /**
     * Get UTM data from context
     * Retrieves tracking data (source, medium, campaign, etc.) for user attribution
     */
    public function getUtmData(): ?array
    {
        $context = $this->getContext();
        return $context['utm_data'] ?? null;
    }
}
