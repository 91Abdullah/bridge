<?php

namespace App;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use phpari;
use channels;
use bridges;
use playbacks;

class TempStasisApp {

    private $phpariObject;
    private $channels;
    private $playbacks;
    private $bridges;
    private $stasisLogger;
    private $dtmfSequence = "";
    private $state;
    private $sequence = ['enter_pin', 'enter_amount', 'verify_amount', 'dial_number'];

    public function __construct($phpariObject)
    {
        try {
            if (is_null($phpariObject)) {
                throw new Exception("[" . __FILE__ . ":" . __LINE__ . "] Stasis application name must be defined!", 500);
            }

            $this->phpariObject = $phpariObject;
            $this->channels = new channels($this->phpariObject);
            $this->playbacks = new playbacks($this->phpariObject);
            $this->bridges = new bridges($this->phpariObject);
            $this->stasisLogger = $this->phpariObject->stasisLogger;

        } catch (Exception $exception) {
            echo $exception->getMessage();
            exit();
        }
    }

    public function executeStasisStart($event)
    {
        $args = $event->args;
        // $this->stasisLogger->notice("+++ App Args +++ " . json_encode($args) . "\n");

        if(empty($args)) {
            //event(new Events\IncomingChannelEvent($this->phpariObject, $event));
            if($this->isAllowed($event->channel->caller->name)) {
                event(new Events\IncomingChannelEvent($this->phpariObject, $event));
            } else {
                event(new Events\UnauthorizeNumberEvent($this->phpariObject, $event));
            }
        }
    }

    public function executeDial($event)
    {
        event(new Events\UpdateRecordEvent($event));
    }

    public function executeChannelLeftBridge($event)
    {
        event(new Events\ChannelHangupEvent($event));

        foreach ($event->bridge->channels as $key => $value) {
            $this->channels->delete($value);
        }

        $this->bridges->terminate($event->bridge->id);
    }

    public function executeChannelDtmfReceived($event)
    {
        $this->setDtmf($event->digit);

        $channel = IncomingChannel::find($event->channel->id);
        $dtmf = null;

        if(!$dtmf = ChannelDtmf::find($event->channel->id)) {
            $dtmf = new ChannelDtmf;
            $dtmf->id = $event->channel->id;
            $dtmf->save();
        } else {
            $dtmf = ChannelDtmf::find($event->channel->id);
        }

        //$this->stasisLogger->notice(dump($this->dtmfSequence));


        if($channel && $channel->state == "initial" && $dtmf !== null) {

            $dtmf->digits = $dtmf->digits . $event->digit;
            $dtmf->save();

            switch ($event->digit) {
                case '#':

                    $digits = substr($this->dtmfSequence, 0, -1);
                    //$digits = substr($dtmf->digits, 0, -1);

                    $this->stasisLogger->info($this->isValidCode($digits));
                    $this->stasisLogger->info($digits);
                    if($this->isValidCode($digits)) {
                        event(new Events\AuthSuccessEvent($this->phpariObject, $event, $digits));
                        $this->dtmfSequence = "";
                        //$this->stasisLogger->notice(dump($digits));
                    } else {
                        event(new Events\AuthNoSuccessEvent($this->phpariObject, $event, $digits));
                        $this->dtmfSequence = "";
                    }

                    break;
                case '*':
                    $this->dtmfSequence = "";
                    $dtmf->digits = "";
                    $dtmf->save();
                    break;
                default:

                    break;
            }
        } elseif($channel && $channel->state == "auth_success" && $dtmf !== null) {

            $dtmf->amount = $dtmf->amount . $event->digit;
            $dtmf->save();

            switch ($event->digit) {
                case '#':

                    $digits = substr($this->dtmfSequence, 0, -1);
                    //$digits = substr($dtmf->amount, 0, -1);

                    $this->stasisLogger->info("+++ Amount Entered: $digits +++");

                    $this->dtmfSequence = "";
                    event(new Events\AmountEnteredEvent($this->phpariObject, $event, $digits));

                    break;
                case '*':
                    $this->dtmfSequence = "";
                    $dtmf->amount = "";
                    $dtmf->save();
                    break;
                default:

                    break;
            }
        } elseif($channel && $channel->state == "amount_validate" && $dtmf !== null) {

            switch ($event->digit) {
                case '1':


                    $this->dtmfSequence = "";
                    event(new Events\AmountCorrectEvent($this->phpariObject, $event));
                    break;

                case '2':

                    try {
                        $this->dtmfSequence = "";
                        event(new Events\AmountIncorrectEvent($this->phpariObject, $event));
                    } catch (Exception $e) {
                        $this->stasisLogger->info($e->getMessage());
                        $this->stasisLogger->info($e->getFile());
                    }
                    break;

                default:
                    break;
            }
        } elseif($channel && $channel->state == "dial_party" && $dtmf !== null) {

            $dtmf->dial = $dtmf->dial . $event->digit;
            $dtmf->save();

            switch ($event->digit) {
                case '#':

                    $digits = substr($dtmf->dial, 0, -1);


                    $this->dtmfSequence = "";
                    event(new Events\OriginateCallEvent($this->phpariObject, $event, $digits));

                    break;
                case '*':
                    $this->dtmfSequence = "";
                    $dtmf->dial = "";
                    $dtmf->save();
                    break;
                default:

                    break;
            }
        }
    }

    public function setDtmf($digit = NULL)
    {
        try {
            $this->dtmfSequence .= $digit;
            return TRUE;
        } catch (Exception $e) {
            return FALSE;
        }
    }

    // Custom Functions

    public function isAllowed($number)
    {
        // $number = str_start($number, '0');
        // $numbers = IncomingNumber::all(['number', 'allowed']);
        // return $numbers->containsStrict(function ($value, $key) use ($number) {
        // return $value['number'] == $number && $value['allowed'];
        // });

        // Test new query dated 20-05-2020 as old one was time taking
        $number = substr($number, 0, 1) == '0' ? substr($number, 1) : $number;
        $status = IncomingNumber::query()->where('number', 'like', "%" . $number . "%")->where('allowed', true)->first();
        return ($status !== null);
    }

    private function isValidCode(string $digits)
    {
        return PinCode::query()->where('code', $digits)->first();
    }

    private function originateCall($event, $number)
    {
        $channel = $event->channel->id;
        $bridge = $this->bridges->create("mixing");
        $this->bridges->addChannel($bridge['id'], $channel);
        $bridge = $this->bridges->details($bridge['id']);


        $bridgeModel = BridgedCall::query()->create([
            "id" => $bridge['id'],
            "bridge_class" => $bridge['bridge_class'],
            "bridge_type" => $bridge['bridge_type'],
            "creator" => $bridge['creator'],
            "channels" => serialize($bridge['channels']),
            "name" => $bridge['name'],
            "technology" => $bridge['technology']
        ]);

        $out_channel = $this->channels->create("SIP/" . $number . "@TCL", "disa-test", $bridge['id']);
        $response = $this->channels->setVariable($out_channel['id'], "CONNECTEDLINE(num)", "2138650001");

        $dial_channel = $this->channels->dial($out_channel['id'], "2138650001", 30);

        $amount = 0;
        $pin = 0;

        try {
            $amount = substr(ChannelDtmf::query()->find($channel)->amount, 0, -1);
            $pin = substr(ChannelDtmf::query()->find($channel)->digits, 0, -1);
        } catch (\Exception $e) {
            $amount = 0;
            $pin = 0;
        }

        $channel = OutgoingChannel::query()->create([
            "id" => $out_channel['id'],
            "name" => $out_channel['name'],
            "language" => $out_channel['language'],
            "accountcode" => $out_channel['accountcode'],
            "creationtime" => $out_channel['creationtime'],
            "state" => $out_channel['state']
        ]);

        $date = Carbon::now();
        $year = $date->year;
        $month = strlen($date->month) == 2 ? $date->month : "0" . $date->month;
        $day = strlen($date->day) == 2 ? $date->day : "0" . $date->day;
        $file_name = $year . "/" . $month . "/" . $day . "/" . $bridge['id'];

        $this->bridges->addChannel($bridge['id'], $out_channel['id']);
        $this->bridges->record($bridge['id'], $file_name, "wav", null, null, null, true, null);
        $record = Record::query()->create([
            'source' => $event->channel->caller->name,
            'destination' => $number,
            'start' => $event->channel->creationtime,
            'bridged_call_id' => $bridge['id'],
            'incoming_channel_id' => $event->channel->id,
            'outgoing_channel_id' => $out_channel['id'],
            "amount" => $amount,
            "pin_code" => $pin,
            'dialstatus' => "NOANSWER"
        ]);
    }
}

