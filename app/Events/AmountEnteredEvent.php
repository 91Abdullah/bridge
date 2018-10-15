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

class AmountEnteredEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $phpariObject;
    public $event;
    public $digits;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($phpariObject, $event, $digits)
    {
        $this->phpariObject = $phpariObject;
        $this->event = $event;
        $this->digits = $digits;
        $channel = IncomingChannel::findOrFail($event->channel->id);
        $channel->state = "amount_validate";
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
