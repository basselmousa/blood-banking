<?php

namespace App\Events;

use App\Models\Donor;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DonorDeferredEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $donor;
    public $reason;
    public $deferralType;
    public $eligibleAfter;

    /**
     * Create a new event instance.
     */
    public function __construct(Donor $donor, string $reason, string $deferralType, ?\DateTime $eligibleAfter)
    {
        $this->donor = $donor;
        $this->reason = $reason;
        $this->deferralType = $deferralType;
        $this->eligibleAfter = $eligibleAfter;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('donors.' . $this->donor->id),
        ];
    }
}
