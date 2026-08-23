<?php

namespace App\Listeners\Billing;

use App\Events\Billing\SubscriptionCreated;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleSubscriptionCreated implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(SubscriptionCreated $event)
    {
        $user = $event->subscription->user;

        // Remove branding — queued to avoid blocking the webhook response.
        $user->workspaces()->with('forms')->get()->each(function ($workspace) {
            $workspace->forms()->update(['no_branding' => true]);
        });
    }
}
