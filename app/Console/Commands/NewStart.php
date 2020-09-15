<?php

namespace App\Console\Commands;

use App\AdvanceStasisApplication;
use App\NewStasisApp;
use Illuminate\Console\Command;

class NewStart extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'new:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start new app based on persistent DTMF entries';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /*$basicAriClient = new AdvanceStasisApplication("disa-test");
        $basicAriClient->stasisLogger->info("Starting Stasis Program... Waiting for handshake...");
        $basicAriClient->StasisAppEventHandler();
        $basicAriClient->stasisLogger->info("Initializing Handlers... Waiting for handshake...");
        $basicAriClient->StasisAppConnectionHandlers();
        $basicAriClient->stasisLogger->info("Connecting... Waiting for handshake...");
        $basicAriClient->execute();*/
        $client = new NewStasisApp("disa-test");
        $client->stasisLogger->info("Starting Stasis Program... Waiting for handshake...");
        $client->StasisAppEventHandler();
        $client->stasisLogger->info("Initializing Handlers... Waiting for handshake...");
        $client->StasisAppConnectionHandlers();
        $client->stasisLogger->info("Connecting... Waiting for handshake...");
        $client->execute();
        return 0;
    }
}
