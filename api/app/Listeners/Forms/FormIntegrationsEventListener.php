<?php

namespace App\Listeners\Forms;

use App\Events\Models\FormIntegrationsEventCreated;
use App\Mail\Forms\FormIntegrationsEventCreationConfirmationMail;
use App\Models\Integration\FormIntegrationsEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class FormIntegrationsEventListener implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(FormIntegrationsEventCreated $event)
    {
        if ($event->formIntegrationsEvent->status === FormIntegrationsEvent::STATUS_ERROR) {
            $form = $event->formIntegrationsEvent->integration->form;
            // M2 FIX: Use Mail::queue() instead of send() — the mailable implements ShouldQueue.
            // send() executes synchronously, blocking the queue worker during SMTP delivery.
            Mail::to($form->creator)->queue(new FormIntegrationsEventCreationConfirmationMail($event->formIntegrationsEvent));
        }
    }
}
