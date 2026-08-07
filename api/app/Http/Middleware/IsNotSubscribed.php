<?php

namespace App\Http\Middleware;

use App\Service\Billing\BillingStateResolver;
use Closure;
use Illuminate\Http\Request;

class IsNotSubscribed
{
    public function __construct(protected BillingStateResolver $billingStateResolver)
    {
    }

    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() && (!pricing_enabled() || $this->billingStateResolver->hasActivePaidSubscription($request->user()))) {
            // This user is a paying customer...
            if ($request->expectsJson()) {
                return response([
                    'message' => 'You already have an active subscription.',
                    'type' => 'error',
                ], 401);
            }

            return redirect('billing');
        }

        return $next($request);
    }
}
