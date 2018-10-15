<?php

namespace App\Listeners;

use App\Events\AuthNoSuccessEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AuthNoSuccessListener
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
     * @param  AuthNoSuccessEvent  $event
     * @return void
     */
    public function handle(AuthNoSuccessEvent $event)
    {
        $event->phpariObject->channels()->playback($event->event->channel->id, "sound:auth-incorrect", null, null, null, null);
    }
}
