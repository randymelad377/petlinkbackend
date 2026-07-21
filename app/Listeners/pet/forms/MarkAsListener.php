<?php

namespace App\Listeners\pet\forms;

use App\Events\pets\forms\MarkAsEvent;
use App\Notifications\pets\forms\MarkAsNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class MarkAsListener
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
    public function handle(MarkAsEvent $event): void
    {
        if (!empty($event->history) || (empty($event->history) && empty($event->transaction)) || ((empty($event->history) && !empty($event->transaction) && ($event->transaction->ownerMarkAsDone === null && $event->transaction->requesterMarkAsDone === null)))) {

            foreach ([$event->sender, $event->receiver] as $user) {
                $user->notify(new MarkAsNotification($event->history, $event->transaction, $event->sender, $event->receiver, $event->action));
            }

            return;
        }

        $event->receiver->notify(new MarkAsNotification($event->history, $event->transaction, $event->sender, $event->receiver, $event->action));
    }
}
