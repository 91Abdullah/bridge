<?php

namespace App\Listeners;

use App\Events\UnauthorizeNumberEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UnauthorizeNumberListener
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
     * @param  UnauthorizeNumberEvent  $event
     * @return void
     */
    public function handle(UnauthorizeNumberEvent $event)
    {
        $event->phpariobject->channels()->playback($event->event->channel->id, "sound:num-not-in-db", null, null, null, null);
        sleep(3);
        $event->phpariobject->channels()->delete($event->event->channel->id);
    }
}
