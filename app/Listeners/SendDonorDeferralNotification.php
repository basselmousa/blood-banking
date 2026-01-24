<?php

namespace App\Listeners;

use App\Events\DonorDeferredEvent;
use App\Notifications\DonorDeferralNotification;

class SendDonorDeferralNotification
{
    /**
     * Handle the event
     */
    public function handle(DonorDeferredEvent $event): void
    {
        $event->donor->notify(
            new DonorDeferralNotification(
                $event->reason,
                $event->deferralType,
                $event->eligibleAfter
            )
        );
    }
}
