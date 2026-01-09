<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\OrderItemReady;
use App\Listeners\CreateNotifyWaiter;
use App\Events\OrderReady;
use App\Events\OrderClaimed;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        OrderItemReady::class => [
            CreateNotifyWaiter::class,
        ],
        OrderReady::class => [
            // listeners could be added later
        ],
        OrderClaimed::class => [
            // listeners could be added later
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
