<?php

namespace App\Console\Commands;

use App\TestStasisApp;
use Illuminate\Console\Command;

class TestApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'testapp:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to start Test App';

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
        $client = new TestStasisApp("disa-test");
        $client->stasisLogger->info("Starting Stasis Program... Waiting for handshake...");
        $client->StasisAppEventHandler();
        $client->stasisLogger->info("Initializing Handlers... Waiting for handshake...");
        $client->StasisAppConnectionHandlers();
        $client->stasisLogger->info("Connecting... Waiting for handshake...");
        $client->execute();
        return 0;
    }
}
