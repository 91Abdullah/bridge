<?php

namespace App;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use phpari;
use channels;
use bridges;
use playbacks;

class SubStasisApp {

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
                $this->channels->playback($event->channel->id, "sound:please-enter-your");
                $this->channels->playback($event->channel->id, "sound:pin_number");
                $this->channels->playback($event->channel->id, "sound:followed_pound");
                $this->state = $this->sequence[0];
            } else {
                $playBackId = "{$event->channel->id}_playback";
                $this->channels->playback($event->channel->id, "sound:num-not-in-db", null, null, null, $playBackId);
                sleep(3);
                $this->channels->delete($event->channel->id);
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
        $digit = $event->digit;
        $channel = $event->channel->id;

        switch ($digit) {
            case "1":
            case "2":
                if($this->state == "verify_amount") {
                    if($digit == "1") {
                        $this->channels->playback($channel, "sound:auth-thankyou");
                        $this->dtmfSequence = "";
                        $this->state = $this->sequence[3];
                    }
                    elseif($digit == "2") {
                        $this->channels->playback($channel, "sound:please-enter-the");
                        $this->channels->playback($channel, "sound:digits");
                        $this->channels->playback($channel, "sound:followed_pound");
                        $this->dtmfSequence = "";
                        $this->state = $this->sequence[1];
                    }
                } else {
                    $this->setDtmf($digit);
                }
                break;
            case "3":
            case "4":
            case "5":
            case "6":
            case "7":
            case "8":
            case "9":
            case "0":
                $this->setDtmf($digit);
                break;
            case "#":
                switch ($this->state) {
                    case "enter_pin":
                        $status = $this->isValidCode($this->dtmfSequence);
                        if($status) {
                            $this->channels->playback($channel, "sound:auth-thankyou");
                            $this->channels->playback($channel, "sound:please-enter-the");
                            $this->channels->playback($channel, "sound:digits");
                            $this->channels->playback($channel, "sound:followed_pound");
                            $this->channels->setVariable($channel, "PIN", $this->dtmfSequence);
                            $this->dtmfSequence = "";
                            $this->state = $this->sequence[1];
                        } else {
                            Log::critical("Invalid auth attempt from: " . $event->channel->caller->name . " with entered PIN: " . $this->dtmfSequence);
                            $this->channels->playback($channel, "sound:auth-incorrect");
                            $this->channels->playback($channel, "sound:please-enter-your");
                            $this->channels->playback($channel, "sound:pin_number");
                            $this->channels->playback($channel, "sound:followed_pound");
                            $this->dtmfSequence = "";
                            $this->state = $this->sequence[0];
                        }
                        break;
                    case "enter_amount":
                        // Add amount in channel var
                        $this->channels->setVariable($channel, "AMOUNT", $this->dtmfSequence);

                        $this->channels->playback($channel, "sound:you-entered");

                        foreach (str_split($this->dtmfSequence) as $key => $value) {
                            $this->channels->playback($channel, "sound:digits/" . $value);
                        }
                        $this->channels->playback($channel, "sound:if-this-is-correct-press");
                        $this->channels->playback($channel, "sound:1-yes-2-no");
                        $this->dtmfSequence = "";
                        $this->state = $this->sequence[2];
                        break;
                    case "dial_number":
                        $this->originateCall($event, $this->dtmfSequence);
                        $this->dtmfSequence = "";
                        break;
                    default:
                        break;
                }
                break;
            case "*":
                $this->dtmfSequence = "";
                break;
            default:
                break;
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

