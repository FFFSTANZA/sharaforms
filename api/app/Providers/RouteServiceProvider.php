<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();
        $this->registerGlobalRouteParamConstraints();

        $this->routes(function () {
            Route::middleware('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('api-external')
                ->group(base_path('routes/api-external.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('auth-login', function (Request $request) {
            $email = strtolower((string) $request->input('email', 'unknown'));

            return [
                Limit::perMinute(5)->by('auth-login:minute:' . sha1($email . '|' . $request->ip())),
                Limit::perHour(20)->by('auth-login:hour:' . sha1($email . '|' . $request->ip())),
            ];
        });

        RateLimiter::for('auth-register', function (Request $request) {
            $email = strtolower((string) $request->input('email', 'unknown'));

            return [
                Limit::perMinute(3)->by('auth-register:minute:' . sha1($email . '|' . $request->ip())),
                Limit::perHour(10)->by('auth-register:hour:' . sha1($email . '|' . $request->ip())),
            ];
        });

        RateLimiter::for('password-email', function (Request $request) {
            $email = strtolower((string) $request->input('email', 'unknown'));

            return [
                Limit::perMinute(3)->by('password-email:minute:' . sha1($email . '|' . $request->ip())),
                Limit::perHour(10)->by('password-email:hour:' . sha1($email . '|' . $request->ip())),
            ];
        });

        RateLimiter::for('password-reset', function (Request $request) {
            $email = strtolower((string) $request->input('email', 'unknown'));

            return [
                Limit::perMinute(5)->by('password-reset:minute:' . sha1($email . '|' . $request->ip())),
                Limit::perHour(20)->by('password-reset:hour:' . sha1($email . '|' . $request->ip())),
            ];
        });

        RateLimiter::for('email-resend', function (Request $request) {
            $email = strtolower((string) $request->input('email', 'unknown'));

            return [
                Limit::perMinute(3)->by('email-resend:minute:' . sha1($email . '|' . $request->ip())),
                Limit::perHour(10)->by('email-resend:hour:' . sha1($email . '|' . $request->ip())),
            ];
        });

        RateLimiter::for('email-verify', function (Request $request) {
            return Limit::perMinute(6)->by('email-verify:' . sha1((string) $request->route('user') . '|' . $request->ip()));
        });

        RateLimiter::for('auth-oidc-options', function (Request $request) {
            $email = strtolower((string) $request->input('email', 'unknown'));

            return [
                Limit::perMinute(5)->by('auth-oidc-options:minute:' . sha1($email . '|' . $request->ip())),
                Limit::perHour(20)->by('auth-oidc-options:hour:' . sha1($email . '|' . $request->ip())),
            ];
        });

        RateLimiter::for('auth-two-factor', function (Request $request) {
            $pendingAuthToken = (string) $request->input('pending_auth_token', 'unknown');

            return [
                Limit::perMinute(5)->by('auth-two-factor:minute:' . sha1($pendingAuthToken . '|' . $request->ip())),
                Limit::perHour(20)->by('auth-two-factor:hour:' . sha1($pendingAuthToken . '|' . $request->ip())),
            ];
        });

        // Rate limit for summary endpoints: 30 requests per minute per user
        RateLimiter::for('summary', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        // Export endpoints use dedicated buckets so long-running CSV exports
        // are not blocked by the general API rate limit.
        RateLimiter::for('export', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('export-status', function (Request $request) {
            return Limit::perMinute(180)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('public-uploads', function (Request $request) {
            $identifier = $request->user()
                ? 'user:' . $request->user()->getAuthIdentifier()
                : 'ip:' . $request->ip();
            $route = $request->route()?->getName() ?? $request->path();
            $key = $route . ':' . $identifier;

            return [
                Limit::perMinute(max(1, config('sharaforms.public_uploads.rate_limit.per_minute', 30)))
                    ->by('public-uploads:minute:' . $key),
                Limit::perHour(max(1, config('sharaforms.public_uploads.rate_limit.per_hour', 300)))
                    ->by('public-uploads:hour:' . $key),
            ];
        });

        // AI form generation endpoints: 4 requests per minute per IP (guests included)
        RateLimiter::for('ai-generate', function (Request $request) {
            $identifier = $request->user()
                ? 'user:' . $request->user()->getAuthIdentifier()
                : 'ip:' . $request->ip();

            return [
                Limit::perMinute(4)->by('ai-generate:minute:' . $identifier),
                Limit::perHour(40)->by('ai-generate:hour:' . $identifier),
            ];
        });

        RateLimiter::for('form-submissions', function (Request $request) {
            $form = $request->route('form');
            $formIdentifier = $form instanceof Model
                ? (string) $form->getKey()
                : (string) $form;
            $identifier = $request->ip();

            return [
                Limit::perMinute(20)->by('form-submissions:minute:' . sha1($formIdentifier . '|' . $identifier)),
                Limit::perHour(200)->by('form-submissions:hour:' . sha1($formIdentifier . '|' . $identifier)),
            ];
        });
    }

    protected function registerGlobalRouteParamConstraints()
    {
        Route::pattern('workspaceId', '[0-9]+');
    }
}
