<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class OutgoingChannelEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $event;
    public $bridge;
    public $phpariObject;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($phpariObject, $bridge, $event)
    {
        $this->event = $event;
        $this->bridge = $bridge;
        $this->phpariObject = $phpariObject;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
