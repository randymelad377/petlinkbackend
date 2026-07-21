<?php

namespace App\Listeners\pet\users;

use App\Events\pets\admin\NewVerifiedPetEvent as AdminNewVerifiedPetEvent;
use App\Events\pets\users\NewVerifiedPetEvent;
use App\Models\User;
use App\Notifications\pets\users\NewVerifiedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NewVerifiedPetListener
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
    public function handle(AdminNewVerifiedPetEvent $event): void
    {
        $users = User::where("user_role_id", 1)->where("id", "!=", $event->user->id)->get();

        foreach ($users as $user) {
            $user->notify(new NewVerifiedNotification($event->pet, $event->user));
        }
    }
}
