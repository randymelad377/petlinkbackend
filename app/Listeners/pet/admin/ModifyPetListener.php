<?php

namespace App\Listeners\pet\admin;

use App\Events\pets\admin\ModifyPetEvent;
use App\Models\User;
use App\Notifications\pets\admin\ModifyPetNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ModifyPetListener
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
    public function handle(ModifyPetEvent $event): void
    {

        $users = null;

        if ($event->action === "cancelTransaction") {
            $users = User::whereIn("id", [$event->user->id, $event->interested->id])->get();
        } else {
            $event->user->notify(new ModifyPetNotification($event->pet, $event->user, $event->action, $event->interested));
            return;
        }

        foreach ($users as $user) {
            $user->notify(new ModifyPetNotification($event->pet, $event->user, $event->action, $event->interested));
        }
    }
}
