<?php

namespace App\Listeners;

use App\Events\UpdateRecordEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Record;

class UpdateRecordListener
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
     * @param  UpdateRecordEvent  $event
     * @return void
     */
    public function handle(UpdateRecordEvent $event)
    {
        $record = Record::where('outgoing_channel_id', $event->event->peer->id)->first();

        if($record) {
            $record->start = $event->event->peer->creationtime;
            $record->dialstatus = $event->event->dialstatus;
            if($record->dialstatus == "ANSWER") 
                $record->answer = $event->event->timestamp;
            $record->save();
        }
    }
}
