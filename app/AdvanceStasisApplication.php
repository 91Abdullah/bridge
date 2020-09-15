<?php

namespace App;

use App\Events\UnauthorizeNumberEvent;
use phpari;
use App\IncomingChannel;
use App\OutgoingChannel;
use Exception;

class AdvanceStasisApplication
{
    private $ariEndpoint;
    private $stasisClient;
    private $stasisLoop;
    private $stasisEvents;
    private $phpariObject;
    private $dtmfSequence = "";
    public $stasisLogger;
    public function __construct($appname = NULL)
    {
        try {
            if (is_null($appname))
                throw new Exception("[" . __FILE__ . ":" . __LINE__ . "] Stasis application name must be defined!", 500);
            $this->phpariObject = new phpari($appname, storage_path("app\\phpari.ini"));
            $this->ariEndpoint  = $this->phpariObject->ariEndpoint;
            $this->stasisClient = $this->phpariObject->stasisClient;
            $this->stasisLoop   = $this->phpariObject->stasisLoop;
            $this->stasisLogger = $this->phpariObject->stasisLogger;
            $this->stasisEvents = $this->phpariObject->stasisEvents;
        } catch (Exception $e) {
            echo $e->getMessage();
            exit(99);
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

    // process stasis events
    public function StasisAppEventHandler()
    {
        $this->stasisEvents->on('StasisStart', function ($event) {
            $this->stasisLogger->notice("+++ StasisStart +++ " . json_encode($event) . "\n");
            // $channel = IncomingChannel::find($event->channel->id);

            $args = $event->args;
            // $this->stasisLogger->notice("+++ App Args +++ " . json_encode($args) . "\n");

            if(empty($args)) {
                //event(new Events\IncomingChannelEvent($this->phpariObject, $event));
                if($this->isAllowed($event->channel->caller->name)) {
                    event(new Events\IncomingChannelEvent($this->phpariObject, $event));
                } else {
                    $this->stasisLogger->info("Unauthorized Number: " . json_encode($event->channel->caller->name));
                    event(new Events\UnauthorizeNumberEvent($this->phpariObject, $event));
                }
            } elseif(!empty($args)) {
                $this->stasisLogger->notice("+++ App Args +++ " . json_encode($args[0]) . "\n");
                // event(new Events\OutgoingChannelEvent($this->phpariObject, $args[0], $event));
            }

        });

        $this->stasisEvents->on('StasisEnd', function ($event) {
            $this->stasisLogger->notice("+++ StasisEnd +++ " . json_encode($event->channel->id) . "\n");
        });

        $this->stasisEvents->on('PlaybackStarted', function ($event) {
            $this->stasisLogger->notice("+++ PlaybackStarted +++ " . json_encode($event->playback->id) . "\n");
        });

        $this->stasisEvents->on('PlaybackFinished', function ($event) {
            $this->stasisLogger->notice("+++ PlaybackFinished +++ " . json_encode($event->playback->id) . "\n");
        });

        $this->stasisEvents->on('ChannelVarset', function ($event) {
            $this->stasisLogger->notice("+++ ChannelVarset +++ " . json_encode($event->channel->id) . "\n");
        });

        $this->stasisEvents->on('Dial', function ($event) {
            $this->stasisLogger->notice("+++ Dial +++ " . json_encode($event->peer->id) . "\n");
            event(new Events\UpdateRecordEvent($event));
        });

        $this->stasisEvents->on('ChannelConnectedLine', function ($event) {
            $this->stasisLogger->notice("+++ ChannelConnectedLine +++ " . json_encode($event->channel->id) . "\n");
        });

        $this->stasisEvents->on('ChannelEnteredBridge', function ($event) {
            $this->stasisLogger->notice("+++ ChannelEnteredBridge +++ " . json_encode($event->channel->id) . "\n");
        });

        $this->stasisEvents->on('ChannelLeftBridge', function ($event) {
            $this->stasisLogger->notice("+++ ChannelLeftBridge +++ " . json_encode($event->bridge->id) . "\n");

            event(new Events\ChannelHangupEvent($event));

            foreach ($event->bridge->channels as $key => $value) {
                $this->stasisLogger->notice("+++ Destroying Channel +++ " . json_encode($value) . "\n");
                $this->phpariObject->channels()->delete($value);
            }

            $this->stasisLogger->notice("+++ Destroying Bridge +++ " . json_encode($event->bridge->id) . "\n");
            $this->phpariObject->bridges()->terminate($event->bridge->id);

        });

        $this->stasisEvents->on('ChannelDtmfReceived', function ($event) {
            $this->stasisLogger->notice("+++ ChannelDtmfReceived +++ [" . json_encode($event->digit) . "]\n");
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


            if($channel && $channel->state == "initial" && $dtmf !== null) {

                $dtmf->digits = $dtmf->digits . $event->digit;
                $dtmf->save();

                switch ($event->digit) {
                    case '#':

                        //$digits = substr($this->dtmfSequence, 0, -1);
                        $digits = substr($dtmf->digits, 0, -1);

                        $this->stasisLogger->info($this->isValidCode($digits));
                        $this->stasisLogger->info($digits);
                        if($this->isValidCode($digits)) {
                            $this->dtmfSequence = "";
                            event(new Events\AuthSuccessEvent($this->phpariObject, $event, $digits));
                        } else {
                            $this->dtmfSequence = "";
                            event(new Events\AuthNoSuccessEvent($this->phpariObject, $event, $digits));
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

        });
    }

    public function isValidCode($code)
    {
        $codes = PinCode::all(['code']);
        return $codes->containsStrict('code', $code);
    }

    public function isAllowed($number)
    {
		// Test new query dated 20-05-2020 as old one was time taking
		$number = substr($number, 0, 1) == '0' ? substr($number, 1) : $number;
		$status = IncomingNumber::where('number', 'like', "%" . $number . "%")->where('allowed', true)->first();
		return ($status !== null);
    }

    public function getAuthCode()
    {
        return "2256";
    }

    public function StasisAppConnectionHandlers()
    {
        try {
            $this->stasisClient->on("request", function ($headers) {
                $this->stasisLogger->notice("Request received!");
            });
            $this->stasisClient->on("handshake", function () {
                $this->stasisLogger->notice("Handshake received!");
            });
            $this->stasisClient->on("message", function ($message) {
                $event = json_decode($message->getData());
                $this->stasisLogger->notice('Received event: ' . $event->type);
                $this->stasisEvents->emit($event->type, array($event));
            });
        } catch (Exception $e) {
            echo $e->getMessage();
            exit(99);
        }
    }
    public function execute()
    {
        try {
            $this->stasisClient->open();
            $this->stasisLoop->run();
        } catch (Exception $e) {
            echo $e->getMessage();
            exit(99);
        }
    }
}
