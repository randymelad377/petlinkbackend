<?php

namespace App\Listeners\admin\users;

use App\Events\admin\users\AdminSendEvent;
use App\Notifications\admin\users\AdminSendNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AdminSendListener
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
    public function handle(AdminSendEvent $event): void
    {
        $event->user->notify(new AdminSendNotification($event->user, $event->message));
    }
}
