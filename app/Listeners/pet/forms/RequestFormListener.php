<?php

namespace App\Listeners\pet\forms;

use App\Events\pets\forms\RequestFormEvent;
use App\Notifications\pets\forms\RequestFormNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class RequestFormListener
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
    public function handle(RequestFormEvent $event): void
    {
        $event->receiver->notify(new RequestFormNotification($event->form, $event->requester, $event->receiver));
    }
}
