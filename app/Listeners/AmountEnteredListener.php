<?php

namespace App\Listeners;

use App\Events\AmountEnteredEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AmountEnteredListener
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
     * @param  AmountEnteredEvent  $event
     * @return void
     */
    public function handle(AmountEnteredEvent $event)
    {
        $event->phpariObject->channels()->playback($event->event->channel->id, "sound:you-entered", null, null, null, null);
        foreach (str_split($event->digits) as $key => $value) {
            $event->phpariObject->channels()->playback($event->event->channel->id, "sound:digits/" . $value, null, null, null, null);
        }
        $event->phpariObject->channels()->playback($event->event->channel->id, "sound:if-this-is-correct-press", null, null, null, null);
        $event->phpariObject->channels()->playback($event->event->channel->id, "sound:1-yes-2-no", null, null, null, null);
    }
}
