<?php

namespace App\Listeners\admin\pets;

use App\Events\admin\pets\SendPetEvent;
use App\Notifications\admin\pets\SendPetNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendPetListener
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
    public function handle(SendPetEvent $event): void
    {
        $event->receiver->notify(new SendPetNotification($event->message, $event->receiver, $event->pet));
    }
}
