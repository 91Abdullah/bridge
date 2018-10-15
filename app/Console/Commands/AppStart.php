<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\BasicStasisApplication;

class AppStart extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start app to get events.';

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
        $basicAriClient = new BasicStasisApplication("disa-test");
        $basicAriClient->stasisLogger->info("Starting Stasis Program... Waiting for handshake...");
        $basicAriClient->StasisAppEventHandler();
        $basicAriClient->stasisLogger->info("Initializing Handlers... Waiting for handshake...");
        $basicAriClient->StasisAppConnectionHandlers();
        $basicAriClient->stasisLogger->info("Connecting... Waiting for handshake...");
        $basicAriClient->execute();
    }
}
