<?php

namespace App\Events\pets\admin;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ModifyPetEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $pet;
    public $user;
    public $action;
    public $interested;


    public function __construct($pet, $user, $action, $interested)
    {
        $this->pet = $pet;
        $this->user = $user;
        $this->action = $action;
        $this->interested = $interested;
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
