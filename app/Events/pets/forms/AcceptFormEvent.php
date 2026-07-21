<?php

namespace App\Events\pets\forms;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AcceptFormEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $transaction;
    public $accepter;
    public $receiver;
    public $action;

    public function __construct($transaction, $accepter, $receiver, $action)
    {
        $this->transaction = $transaction;
        $this->accepter = $accepter;
        $this->receiver = $receiver;
        $this->action = $action;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
