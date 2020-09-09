<?php

namespace App\Jobs;

use App\AdvanceStasisApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Artisan;

class IncomingCallJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $basicAriClient = new AdvanceStasisApplication("disa-test");
        $basicAriClient->stasisLogger->info("Starting Stasis Program... Waiting for handshake...");
        $basicAriClient->StasisAppEventHandler();
        $basicAriClient->stasisLogger->info("Initializing Handlers... Waiting for handshake...");
        $basicAriClient->StasisAppConnectionHandlers();
        $basicAriClient->stasisLogger->info("Connecting... Waiting for handshake...");
        $basicAriClient->execute();
    }
}
