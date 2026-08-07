<?php

namespace App\Http\Middleware;

use App\Service\Billing\BillingStateResolver;
use Closure;
use Illuminate\Http\Request;

class IsSubscribed
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
        if ($request->user() && pricing_enabled() && !$this->billingStateResolver->hasActivePaidSubscription($request->user())) {
            // This user is not a paying customer...
            if ($request->expectsJson()) {
                return response([
                    'message' => 'An active subscription is required for this action.',
                    'type' => 'error',
                ], 401);
            }

            return redirect('billing');
        }

        return $next($request);
    }
}
