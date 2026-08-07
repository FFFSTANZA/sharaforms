<?php

namespace App\Listeners\Billing;

use App\Events\Billing\SubscriptionUpdated;
use App\Service\Billing\BillingStateResolver;
use Illuminate\Contracts\Queue\ShouldQueue;

class RevertBrandingOnSubscriptionEnd implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct(protected BillingStateResolver $billingStateResolver)
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SubscriptionUpdated $event): void
    {
        $user = $event->subscription->user;

        if (!$user
            || $event->subscription->valid()
            || $this->billingStateResolver->hasActivePaidSubscription($user)) {
            return;
        }

        $user->workspaces()->with('forms')->get()->each(function ($workspace) {
            $workspace->forms()->update(['no_branding' => false]);
        });
    }
}
