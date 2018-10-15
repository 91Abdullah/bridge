<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\IncomingChannel;

class OriginateCallEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $number;
    public $phpariObject;
    public $event;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($phpariObject, $event, $number)
    {
        $this->number = $number;
        $this->phpariObject = $phpariObject;
        $this->event = $event;
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
