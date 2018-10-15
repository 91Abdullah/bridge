<?php

namespace App\Listeners;

use App\Events\IncomingChannelEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class IncomingChannelListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  IncomingChannelEvent  $event
     * @return void
     */
    public function handle(IncomingChannelEvent $event)
    {
        $event->phpariObject->channels()->answer($event->channel->id);
        $event->phpariObject->channels()->playback($event->channel->id, "sound:please-enter-your", null, null, null, null);
        $event->phpariObject->channels()->playback($event->channel->id, "sound:pin_number", null, null, null, null);
        $event->phpariObject->channels()->playback($event->channel->id, "sound:followed_pound", null, null, null, null);
    }
}
