<?php

namespace App\Events;

use App\IncomingChannel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class AmountIncorrectEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $event;
    public $phpariObject;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($phpariObject, $event)
    {
        $this->event = $event;
        $this->phpariObject = $phpariObject;
        $channel = IncomingChannel::findOrFail($event->channel->id);
        $channel->state = "auth_success";
        $channel->save();
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
