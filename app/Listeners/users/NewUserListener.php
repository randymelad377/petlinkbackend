<?php

namespace App\Listeners\users;

use App\Events\users\NewUserEvent;
use App\Models\User;
use App\Notifications\users\NewUserNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NewUserListener
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
    public function handle(NewUserEvent $event): void
    {
        $user = User::where("user_role_id", 3)->first();

        $user->notify(new NewUserNotification($event->user));
    }
}
