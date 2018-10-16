<?php

namespace App\Listeners;

use App\Events\AmountCorrectEvent;
use App\Record;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AmountCorrectListener
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
     * @param  AmountCorrectEvent  $event
     * @return void
     */
    public function handle(AmountCorrectEvent $event)
    {
        $event->phpariObject->channels()->playback($event->event->channel->id, "sound:auth-thankyou", null, null, null, null);
        // $event->phpariObject->channels()->playback($event->event->channel->id, "tone:dial;tonezone=fr", null, null, null, "tone");
    }
}
