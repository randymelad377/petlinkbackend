<?php

namespace App\Listeners\pet\forms;

use App\Events\pets\forms\AcceptFormEvent;
use App\Notifications\pets\forms\AcceptFormNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AcceptFormListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AcceptFormEvent $event): void
    {
        $event->receiver->notify(new AcceptFormNotification($event->transaction, $event->accepter, $event->receiver, $event->action));
    }
}
