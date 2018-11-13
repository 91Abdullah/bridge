<?php

namespace App\Listeners;

use App\Events\ChannelHangupEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Record;
use Carbon\Carbon;

class ChannelHangupListener
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
     * @param  ChannelHangupEvent  $event
     * @return void
     */
    public function handle(ChannelHangupEvent $event)
    {
        $record = Record::where('bridged_call_id', $event->event->bridge->id)->first();
        $record->end = $event->event->timestamp;
        $record->duration = Carbon::parse($event->event->timestamp)->diffInSeconds($record->start);
        $record->billsec = $record->answer == '' ? 0 : Carbon::parse($event->event->timestamp)->diffInSeconds($record->answer);
        $record->save();
    }
}
