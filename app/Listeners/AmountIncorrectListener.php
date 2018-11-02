<?php

namespace App\Listeners;

use App\Events\AmountIncorrectEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AmountIncorrectListener
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
     * @param  AmountIncorrectEvent  $event
     * @return void
     */
    public function handle(AmountIncorrectEvent $event)
    {
        $event->phpariObject->channels()->playback($event->event->channel->id, "sound:please-enter-the", null, null, null, null);
        $event->phpariObject->channels()->playback($event->event->channel->id, "sound:digits", null, null, null, null);
        $event->phpariObject->channels()->playback($event->event->channel->id, "sound:followed_pound", null, null, null, null);
    }
}
