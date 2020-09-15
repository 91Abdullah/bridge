<?php

namespace App;

use bridges;
use channels;
use Exception;
use playbacks;
use Carbon\Carbon;
class DISA
{
    protected $ivrflow = ['authorization', 'cheque_amount', 'verify_amount', 'customer_number'];
    public $currentStep;
    public $dtmfSequence = "";

    public $source = "";
    public $destination = "";
    public $start = "";
    public $end = "";
    public $answer = "";
    public $duration = "";
    public $billsec = "";
    public $status = "";
    public $amount = "";
    public $pin_code = "";
    public $bridged_call_id = "";
    public $incoming_channel_id = "";
    public $outgoing_channel_id = "";

    public $currentPlayback;

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
            $this->currentStep = $this->ivrflow[0];

        } catch (Exception $exception) {
            echo $exception->getMessage();
            exit();
        }
    }

    public function start($event)
    {
        $this->stasisLogger->notice(json_encode($event));
        if($this->checkAuth($event->channel->caller->name)) {

            $this->channels->answer($event->channel->id);
            $this->start = Carbon::now();
            $this->source = $event->channel->caller->number;
            $this->status = 'NO ANSWER';

            $this->channels->playback($event->channel->id, 'sound:ari/pincode', 'en', 0, 5000);
            $this->channels->playback($event->channel->id, 'sound:ari/reset_code', 'en', 0, 5000);
            $this->channels->playback($event->channel->id, 'sound:ari/star', 'en', 0, 5000);
        } else {
            $this->channels->playback($event->channel->id, 'sound:ari/unauthorize', 'en', 0, 10000);
            //$this->channels->delete($event->channel->id);
        }
    }

    public function onDTMFDigit($event)
    {
        if($this->currentPlayback) {
            $this->playbacks->remove($this->currentPlayback);
        }
        switch ($event->digit) {
            case "#":
                $this->stasisLogger->notice("# Enter and sequence is {$this->dtmfSequence}");
                $this->stasisLogger->notice("# Enter and step is {$this->currentStep}");
                switch ($this->currentStep) {
                    case 'authorization':
                        if($this->checkPINCode($this->dtmfSequence)) {
                            $this->stasisLogger->notice("Client {$event->channel->id} has been authorized");
                            $this->stasisLogger->notice("Client {$event->channel->id} will move on to step 2");
                            //$this->pin_code = substr($this->dtmfSequence, 0, 4);
                            $this->pin_code = $this->dtmfSequence;
                            $this->resetDTMF();
                            $this->currentStep = $this->ivrflow[1];
                            $this->channels->playback($event->channel->id, 'sound:ari/amount', 'en', 0, 5000);
                        } else {
                            $this->channels->playback($event->channel->id, 'sound:ari/invalid_code', 'en', 0, 5000);
                            $this->resetDTMF();
                        }
                        break;
                    case 'cheque_amount':
                        $this->stasisLogger->notice("Client {$event->channel->id} entered cheque amount of {$this->dtmfSequence}");
                        $this->channels->playback($event->channel->id, 'sound:ari/amount_entered_is', 'en', 0, 5000);
                        foreach (str_split($this->dtmfSequence) as $item) {
                            $this->channels->playback($event->channel->id, "sound:digits/$item", 'en', 0, 5000);
                        }
                        $this->channels->playback($event->channel->id, 'sound:ari/if_correct', 'en', 0, 5000);
                        $this->stasisLogger->notice("Client {$event->channel->id} will move on to step 3");
                        $this->currentStep = $this->ivrflow[2];
                        $this->amount = $this->dtmfSequence;
                        break;
                    case 'customer_number':
                        $this->stasisLogger->notice("Client {$event->channel->id} entered customer number {$this->dtmfSequence}");
                        // Dial number
                        $this->dialNumber($this->dtmfSequence, $event->channel->id);
                        break;
                    default:
                        $this->channels->playback($event->channel->id, 'sound:ari/invalid_code', 'en', 0, 5000);
                        break;
                }
                break;
            case "*":
                if($this->currentStep === $this->ivrflow[3]) {
                    $this->hangupCall($event);
                } else {
                    $this->resetDTMF();
                }
                break;
            default:
                $this->dtmfSequence .= $event->digit;
                $this->stasisLogger->notice("sequence is {$this->dtmfSequence}");
                if ($this->currentStep === $this->ivrflow[2]) {
                    switch ($event->digit) {
                        case '1':
                            $this->stasisLogger->notice("Client {$event->channel->id} entered correct amount");
                            $this->stasisLogger->notice("Client {$event->channel->id} will move on to step 4");
                            $this->currentStep = $this->ivrflow[3];
                            $this->resetDTMF();
                            $this->channels->playback($event->channel->id, 'sound:ari/thank_you', 'en', 0, 5000);
                            $this->channels->playback($event->channel->id, "sound:ari/customer_number", 'en', 0, 5000);
                            $this->channels->playback($event->channel->id, "sound:ari/incall_hangup", 'en', 0, 5000);
                            break;
                        case '2':
                            $this->stasisLogger->notice("Client {$event->channel->id} entered incorrect amount");
                            $this->stasisLogger->notice("Client {$event->channel->id} will be pushed back to step 2");
                            $this->resetDTMF();
                            $this->currentStep = $this->ivrflow[1];
                            $this->playbackStep2($event);
                            break;
                        default:
                            $this->channels->playback($event->channel->id, 'sound:ari/invalid_code', 'en', 0, 5000);
                            break;
                    }
                }
                break;
        }
    }

    public function onPlaybackFinished($event)
    {
        if($event->playback->media_uri === "sound:ari/unauthorize") {
            $channel = explode(":", $event->playback->target_uri)[1];
            $this->channels->delete($channel);
        }
    }

    public function onChannelLeftBridge($event)
    {
        foreach ($event->bridge->channels as $channel) {
            $this->channels->delete($channel);
            //$this->bridges->removeChannel($channel);
        }

        $this->bridges->terminate($event->bridge->id);

        $this->end = Carbon::now();
        $this->duration = Carbon::parse($this->start, 'Asia/Karachi')->diffInSeconds($this->end);
        $this->billsec = $this->status === "ANSWERED" && $this->answer !== "" ? Carbon::parse($this->answer, 'Asia/Karachi')->diffInSeconds($this->end) : "";
        $this->stasisLogger->notice("Variables: $this->source, $this->destination, $this->start, $this->answer, $this->end, $this->duration, $this->billsec, $this->billsec, $this->start, $this->amount, $this->pin_code");
        $record = new Record;
        $record->start = $this->start;
        $record->answer = $this->answer;
        $record->end = $this->end;
        $record->source = $this->source;
        $record->destination = $this->destination;
        $record->duration = $this->duration;
        $record->billsec = $this->billsec;
        $record->pin_code = $this->pin_code;
        $record->amount = $this->amount;
        $record->dialstatus = $this->status;
        $record->bridged_call_id = $this->bridged_call_id;
        $record->incoming_channel_id = $this->incoming_channel_id;
        $record->outgoing_channel_id = $this->outgoing_channel_id;
        $record->save();
    }

    public function onChannelStateChange($event)
    {
        $this->stasisLogger->notice("Channel State: {$event->channel->state}");
        if($event->channel->state === "Up") {
            $this->answer = Carbon::now();
            $this->status = "ANSWERED";
        }
    }

    private function checkAuth($number)
    {
        // Test new query dated 20-05-2020 as old one was time taking
        $number = substr($number, 0, 1) === '0' ? substr($number, 1) : $number;
        $status = IncomingNumber::query()->where('number', 'like', "%" . $number . "%")->where('allowed', true)->first();
        return ($status !== null);
    }

    private function checkPINCode($digits)
    {
        $code = PinCode::query()->where('code', $digits)->first();
        return $code !== null;
    }

    private function resetDTMF()
    {
        $this->stasisLogger->notice('Resetting code...');
        $this->dtmfSequence = "";
    }

    private function playbackStep2($event)
    {
        $this->channels->playback($event->channel->id, 'sound:ari/amount', 'en', 0, 5000);
    }

    private function dialNumber($number, $channel)
    {
        $bridge = $this->bridges->create('mixing');
        $this->bridges->addChannel($bridge['id'], $channel);
        $outChannel = $this->channels->create("SIP/$number@TCL", "disa-test", "customer_number|$channel");
        $this->bridges->addChannel($bridge['id'], $outChannel['id']);
        $this->channels->dial($outChannel['id'], null, 60);

        /*** Recording Object ***/

        $date = Carbon::now();
        $year = $date->year;
        $month = strlen($date->month) == 2 ? $date->month : "0" . $date->month;
        $day = strlen($date->day) == 2 ? $date->day : "0" . $date->day;
        $file_name = $year . "/" . $month . "/" . $day . "/" . $bridge['id'];
        $this->bridges->record($bridge['id'], $file_name, "wav", null, null, null, true, null);

        /*** Recording Object ***/

        $this->destination = $number;
        $this->bridged_call_id = $bridge['id'];
        $this->incoming_channel_id = $channel;
        $this->outgoing_channel_id = $outChannel['id'];
    }

    private function hangupCall($event)
    {
        $this->stasisLogger->notice("Client {$this->incoming_channel_id} has requested hangup...");
        $this->channels->delete($this->outgoing_channel_id);
        $this->bridges->terminate($this->bridged_call_id);
        $this->resetDTMF();
        $this->currentStep = $this->ivrflow[3];
        $this->channels->playback($event->channel->id, "sound:ari/customer_number", 'en', 0, 5000);
        $this->channels->playback($event->channel->id, "sound:ari/incall_hangup", 'en', 0, 5000);
    }
}
