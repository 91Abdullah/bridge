<?php

namespace App\Listeners;

use App\Events\AuthSuccessEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AuthSuccessListener
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
     * @param  AuthSucessEvent  $event
     * @return void
     */
    public function handle(AuthSuccessEvent $event)
    {
        $event->phpariObject->channels()->playback($event->event->channel->id, "sound:auth-thankyou", null, null, null, null);
        // $event->phpariObject->channels()->playback($event->channel->id, "tone:ring;tonezone=en", null, null, null, null);
        $event->phpariObject->channels()->playback($event->event->channel->id, "sound:please-enter-the", null, null, null, null);
        $event->phpariObject->channels()->playback($event->event->channel->id, "sound:digits", null, null, null, null);
        $event->phpariObject->channels()->playback($event->event->channel->id, "sound:followed_pound", null, null, null, null);
    }
}
