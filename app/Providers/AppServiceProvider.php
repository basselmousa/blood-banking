<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Http\View\Composers\DonationEligibilityComposer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Register view composers for donation eligibility views
        view()->composer([
            'donation.eligibility.dashboard',
            'donation.eligibility.check',
            'donation.eligibility.list',
            'donation.deferrals.list',
            'donation.donations.record',
        ], DonationEligibilityComposer::class);
    }
}
