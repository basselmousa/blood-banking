<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\DonorDeferredEvent;
use App\Events\DonationRecordedEvent;
use App\Listeners\SendDonorDeferralNotification;
use App\Listeners\SendDonationThankYouNotification;

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
        DonorDeferredEvent::class => [
            SendDonorDeferralNotification::class,
        ],
        DonationRecordedEvent::class => [
            SendDonationThankYouNotification::class,
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
