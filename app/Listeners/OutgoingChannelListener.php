<?php

namespace App\Listeners;

use App\Events\OutgoingChannelEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class OutgoingChannelListener
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
     * @param  OutgoingChannelEvent  $event
     * @return void
     */
    public function handle(OutgoingChannelEvent $event)
    {
        $event->phpariObject->channels()->answer($event->event->channel->id);
        try {
            $response = $event->phpariObject->bridges()->addChannel($event->bridge, $event->event->channel->id);
        } catch (Exception $e) {
            $event->phpariObject->stasisLogger->notice("+++ Last error: " . $e->lasterror . " +++");
            $event->phpariObject->stasisLogger->notice("+++ Last trace: " . $e->lasttrace . " +++");
        }
        $bridge = $event->phpariObject->bridges()->details($event->bridge); 
        $event->phpariObject->stasisLogger->notice("+++ Last Response: " . $response . " +++");   
        $event->phpariObject->stasisLogger->notice("+++ Channel Added to Bridge: " . $event->event->channel->id . " +++");
        $event->phpariObject->stasisLogger->notice("+++ Channel Indicate Ringing Stop: " . $bridge['channels'][0] . " +++");
        $event->phpariObject->channels()->indicateRingingStop($bridge['channels'][0]);
    }
}
