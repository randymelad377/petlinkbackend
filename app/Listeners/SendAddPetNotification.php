<?php

namespace App\Listeners;

use App\Events\pets\users\AddPetEvent;
use App\Models\User;
use App\Notifications\pets\users\AddPetNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendAddPetNotification
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
    public function handle(AddPetEvent $event): void
    {
        $user = User::where("user_role_id", 3)->first();

        $user->notify(new AddPetNotification($event->user, $event->pet_id));
    }
}
