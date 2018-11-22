<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        'App\Events\IncomingChannelEvent' => [
            'App\Listeners\IncomingChannelListener'
        ],
        'App\Events\AuthSuccessEvent' => [
            'App\Listeners\AuthSuccessListener'
        ],
        'App\Events\AuthNoSuccessEvent' => [
            'App\Listeners\AuthNoSuccessListener'
        ],
        'App\Events\AmountEnteredEvent' => [
            'App\Listeners\AmountEnteredListener'
        ],
        'App\Events\AmountCorrectEvent' => [
            'App\Listeners\AmountCorrectListener'
        ],
        'App\Events\OriginateCallEvent' => [
            'App\Listeners\OriginateCallListener'
        ],
        'App\Events\OutgoingChannelEvent' => [
            'App\Listeners\OutgoingChannelListener'
        ],
        'App\Events\UpdateRecordEvent' => [
            'App\Listeners\UpdateRecordListener'
        ],
        'App\Events\ChannelHangupEvent' => [
            'App\Listeners\ChannelHangupListener'
        ],
        'App\Events\AmountIncorrectEvent' => [
            'App\Listeners\AmountIncorrectListener'
        ],
        'App\Events\UnauthorizeNumberEvent' => [
            'App\Listeners\UnauthorizeNumberListener'
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
