<?php

namespace App\Listeners\others;

use App\Events\others\ConcernEvent;
use App\Models\User;
use App\Notifications\others\ConcernNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ConcernListener
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
    public function handle(ConcernEvent $event): void
    {
        $user = User::where("user_role_id", 3)->first();

        $user->notify(new ConcernNotification($event->user));
    }
}
