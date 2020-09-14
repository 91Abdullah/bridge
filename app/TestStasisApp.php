<?php

namespace App;

use events;
use Exception;
use phpari;

class TestStasisApp {

    private $phpariObject;
    private $stasisClient;
    private $stasisLoop;
    public $stasisLogger;
    private $stasisEvents;
    private $clients = [];
    private $events;

    public function __construct($appName = NULL)
    {
        try {
            if (is_null($appName)) {
                throw new Exception("[" . __FILE__ . ":" . __LINE__ . "] Stasis application name must be defined!", 500);
            }

            $this->phpariObject = new phpari($appName, storage_path("app\\phpari.ini"));
            $this->ariEndpoint  = $this->phpariObject->ariEndpoint;
            $this->stasisClient = $this->phpariObject->stasisClient;
            $this->stasisLoop   = $this->phpariObject->stasisLoop;
            $this->stasisLogger = $this->phpariObject->stasisLogger;
            $this->stasisEvents = $this->phpariObject->stasisEvents;

            $this->events = new events($this->phpariObject);

        } catch (Exception $exception) {
            echo $exception->getMessage();
            exit();
        }
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
            echo $e->getLine();
            exit(99);
        }
    }
    public function execute()
    {
        try {
            $this->stasisClient->open();
            $this->stasisLoop->run();
        } catch (Exception $e) {
            $this->stasisLogger->info(json_encode($e->getMessage()));
            $this->stasisLogger->info(json_encode($e->getLine()));
            $this->stasisLogger->info(json_encode($e->getTraceAsString()));
            exit(99);
        }
    }

    public function StasisAppEventHandler()
    {
        $this->stasisEvents->on('StasisStart', function ($event) {
            $this->stasisLogger->notice("+++ Starting Client for +++ [{$event->channel->id}]");
            if($event->args === []) {
                $this->clients[$event->channel->id] = new DISA($this->phpariObject);
                $this->clients[$event->channel->id]->start($event);
            }
        });

        $this->stasisEvents->on('Dial', function ($event) {
            $this->stasisLogger->notice("+++ Dial +++ [" . json_encode($event->peer->id) . "]\n");
        });

        $this->stasisEvents->on('ChannelLeftBridge', function ($event) {
            $this->stasisLogger->notice("+++ ChannelLeftBridge +++ [" . json_encode($event->bridge->id) . "]\n");
            if(array_key_exists($event->channel->id, $this->clients)) {
                $this->clients[$event->channel->id]->onChannelLeftBridge($event);
            }
        });

        $this->stasisEvents->on('ChannelStateChange', function ($event) {
            $this->stasisLogger->notice("+++ ChannelStateChange +++ [" . $event->channel->id . "]\n");
            if(str_contains($event->channel->dialplan->app_data, "|")) {
                $this->clients[explode("|", $event->channel->dialplan->app_data)[1]]->onChannelStateChange($event);
            }
        });

        $this->stasisEvents->on('ChannelDtmfReceived', function ($event) {
            $this->stasisLogger->notice("+++ ChannelDtmfReceived +++ [" . json_encode($event->digit) . "]\n");
            $this->clients[$event->channel->id]->onDTMFDigit($event);
        });

        $this->stasisEvents->on('StasisEnd', function ($event) {
            unset($this->clients[$event->channel->id]);
            $this->stasisLogger->info("+++ Clients connected +++ [" . json_encode(count($this->clients) . "]\n"));
        });

        $this->stasisEvents->on('PlaybackFinished', function ($event) {
            $this->clients[explode(":", $event->playback->target_uri)[1]]->onPlaybackFinished($event);
        });

        $this->stasisEvents->on('PlaybackStarted', function ($event) {
            $this->stasisLogger->info("+++ Playback ID: " . json_encode($event));
            $this->clients[explode(":", $event->playback->target_uri)[1]]->currentPlayback = $event->playback->id;
        });
    }
}
