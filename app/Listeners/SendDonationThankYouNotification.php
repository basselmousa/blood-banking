<?php

namespace App\Listeners;

use App\Events\DonationRecordedEvent;
use App\Notifications\DonationThankYouNotification;

class SendDonationThankYouNotification
{
    /**
     * Handle the event
     */
    public function handle(DonationRecordedEvent $event): void
    {
        $event->donationRecord->donor->notify(
            new DonationThankYouNotification($event->donationRecord)
        );
    }
}
