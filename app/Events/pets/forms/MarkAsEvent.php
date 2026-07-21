<?php

namespace App\Events\pets\forms;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MarkAsEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $history;
    public $transaction;
    public $sender;
    public $receiver;
    public $action;

    public function __construct($history, $transaction, $sender, $receiver, $action)
    {
        $this->history = $history;
        $this->transaction = $transaction;
        $this->sender = $sender;
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
