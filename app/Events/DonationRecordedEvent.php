<?php

namespace App\Events;

use App\Models\DonationRecord;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DonationRecordedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $donationRecord;

    /**
     * Create a new event instance.
     */
    public function __construct(DonationRecord $donationRecord)
    {
        $this->donationRecord = $donationRecord;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('donations.' . $this->donationRecord->donor_id),
        ];
    }
}
