<?php

namespace App\Jobs\SaaS;

use App\Models\PushNotification;
use App\Services\PushNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $notificationId;
    public $tries = 3;
    public $timeout = 30;

    public function __construct($notificationId)
    {
        $this->notificationId = $notificationId;
    }

    public function handle(PushNotificationService $pushNotificationService)
    {
        $notification = PushNotification::find($this->notificationId);

        if (!$notification) {
            return;
        }

        try {
            // Send via push service
            $result = $pushNotificationService->sendViaPushService($notification);

            if ($result) {
                $notification->markAsSent();
            } else {
                $notification->markAsFailed('Failed to deliver via push service');
            }
        } catch (\Exception $e) {
            $notification->markAsFailed($e->getMessage());
            throw $e;
        }
    }

    public function failed(\Exception $exception)
    {
        $notification = PushNotification::find($this->notificationId);

        if ($notification) {
            $notification->markAsFailed('Job failed: ' . $exception->getMessage());
        }
    }
}
