<?php

namespace App\Listeners;

use App\ChannelDtmf;
use App\Events\OriginateCallEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\OutgoingChannel;
use App\BridgedCall;
use App\Record;

class OriginateCallListener
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
     * @param  OriginateCallEvent  $event
     * @return void
     */
    public function handle(OriginateCallEvent $event)
    {
        $bridge = $event->phpariObject->bridges()->create("mixing");
        $event->phpariObject->bridges()->addChannel($bridge['id'], $event->event->channel->id);
        // $event->phpariObject->bridges()->addChannel($bridge['id'], $channel['id']);
        $bridge = $event->phpariObject->bridges()->details($bridge['id']);
        

        $bridgeModel = BridgedCall::create([
            "id" => $bridge['id'],
            "bridge_class" => $bridge['bridge_class'],
            "bridge_type" => $bridge['bridge_type'],
            "creator" => $bridge['creator'],
            "channels" => serialize($bridge['channels']),
            "name" => $bridge['name'],
            "technology" => $bridge['technology']
        ]);

        // $out_channel = $event->phpariObject->channels()->originate("SIP/" . $event->number . "@TCL", null, [
        //     "app" => "disa-test",
        //     "appArgs" => $bridge['id'],
        //     "timeout" => 20
        // ]);

        $out_channel = $event->phpariObject->channels()->create("SIP/" . $event->number . "@TCL", "disa-test", $bridge['id']);
        $response = $event->phpariObject->channels()->setVariable($out_channel['id'], "CONNECTEDLINE(num)", "2138650001");

        $event->phpariObject->stasisLogger->notice(dump($response));

        // $dial_channel = $event->phpariObject->channels()->dial($out_channel['id'], $event->event->channel->id, 30);
        $dial_channel = $event->phpariObject->channels()->dial($out_channel['id'], "2138650001", 30);

        //$amount = $event->phpariObject->channels()->getVariable($event->event->channel->id, "AMOUNT");
        //$pin = $event->phpariObject->channels()->getVariable($event->event->channel->id, "PIN");

        $amount = 0;
        $pin = 0;

        try {
            $amount = substr(ChannelDtmf::find($event->event->channel->id)->amount, 0, -1);
            $pin = substr(ChannelDtmf::find($event->event->channel->id)->digits, 0, -1);
        } catch (\Exception $e) {
            $amount = 0;
            $pin = 0;
        }

        $event->phpariObject->stasisLogger->notice(dump($pin));

        $channel = OutgoingChannel::create([
            "id" => $out_channel['id'],
            "name" => $out_channel['name'],
            "language" => $out_channel['language'],
            "accountcode" => $out_channel['accountcode'],
            "creationtime" => $out_channel['creationtime'],
            "state" => $out_channel['state']
        ]);

        // $event->phpariObject->channels()->indicateRingingStart($event->event->channel->id);
        $event->phpariObject->bridges()->addChannel($bridge['id'], $out_channel['id']);
        $event->phpariObject->bridges()->record($bridge['id'], $bridge['id'], "wav", null, null, null, true, null);
        $record = Record::create([
            'source' => $event->event->channel->caller->name,
            'destination' => $event->number,
            'start' => $event->event->channel->creationtime,
            'bridged_call_id' => $bridge['id'],
            'incoming_channel_id' => $event->event->channel->id,
            'outgoing_channel_id' => $out_channel['id'],
            "amount" => $amount,
            "pin_code" => $pin,
            'dialstatus' => "NOANSWER"
        ]);
    }
}
