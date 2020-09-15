<?php

namespace App;

use App\Events\UpdateRecordEvent;
use Exception;
use phpari;
use events;

class NewStasisApp {

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
                //$this->stasisLogger->notice('Received event: ' . $event->type);
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
            $this->stasisLogger->notice("Starting Client for " . $event->channel->id);
            $this->clients[$event->channel->id] = new TempStasisApp($this->phpariObject);
            $this->clients[$event->channel->id]->executeStasisStart($event);
        });

        $this->stasisEvents->on('Dial', function ($event) {
            $this->stasisLogger->notice("+++ Dial +++ " . json_encode($event->peer->id) . "\n");
            //$this->clients[$event->channel->id]->executeDial($event);
            event(new UpdateRecordEvent($event));
        });

        $this->stasisEvents->on('ChannelLeftBridge', function ($event) {
            $this->stasisLogger->notice("+++ ChannelLeftBridge +++ " . json_encode($event->bridge->id) . "\n");
            $this->clients[$event->channel->id]->executeChannelLeftBridge($event);
        });

        $this->stasisEvents->on('ChannelDtmfReceived', function ($event) {
            //$this->stasisLogger->notice("+++ ChannelDtmfReceived +++ [" . json_encode($event->digit) . "]\n");
            $this->clients[$event->channel->id]->executeChannelDtmfReceived($event);
        });

        $this->stasisEvents->on('StasisEnd', function ($event) {
            unset($this->clients[$event->channel->id]);
            $this->stasisLogger->info("Clients connected: " . json_encode(count($this->clients)));
        });
    }
}

