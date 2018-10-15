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

class IncomingChannelEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $phpariObject;
    public $channel;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($phpariObject, $event)
    {
        $this->phpariObject = $phpariObject;
        $this->channel = IncomingChannel::create([
            'id' => $event->channel->id,
            'name' => $event->channel->name,
            'accountcode' => $event->channel->accountcode,
            'language' => $event->channel->language,
            'creationtime' => $event->channel->creationtime,
            'state' => 'initial'
        ]);
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
