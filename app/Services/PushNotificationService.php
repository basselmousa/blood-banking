<?php

namespace App\Services;

use App\Models\PushNotification;
use App\Models\MobileDevice;
use Illuminate\Support\Facades\Http;

class PushNotificationService
{
    public function sendNotification($deviceId, $title, $body, $type = 'custom', $actionUrl = null, $data = null)
    {
        $device = MobileDevice::where('device_id', $deviceId)
            ->where('notifications_enabled', true)
            ->firstOrFail();

        $notification = PushNotification::create([
            'tenant_id' => $device->tenant_id,
            'user_id' => $device->user_id,
            'mobile_device_id' => $device->id,
            'title' => $title,
            'body' => $body,
            'notification_type' => $type,
            'action_url' => $actionUrl,
            'data' => $data,
            'status' => 'pending',
        ]);

        // Dispatch job to send notification
        dispatch(new \App\Jobs\SendPushNotification($notification->id));

        return $notification;
    }

    public function sendBulkNotifications($userIds, $tenantId, $title, $body, $type = 'custom', $data = null)
    {
        $devices = MobileDevice::whereIn('user_id', $userIds)
            ->where('tenant_id', $tenantId)
            ->where('notifications_enabled', true)
            ->get();

        $notifications = [];

        foreach ($devices as $device) {
            $notification = PushNotification::create([
                'tenant_id' => $tenantId,
                'user_id' => $device->user_id,
                'mobile_device_id' => $device->id,
                'title' => $title,
                'body' => $body,
                'notification_type' => $type,
                'data' => $data,
                'status' => 'pending',
            ]);

            $notifications[] = $notification->id;
        }

        // Dispatch bulk send job
        foreach ($notifications as $notificationId) {
            dispatch(new \App\Jobs\SendPushNotification($notificationId));
        }

        return [
            'total_sent' => count($notifications),
            'notification_ids' => $notifications,
        ];
    }

    public function sendToTenantUsers($tenantId, $title, $body, $type = 'custom', $data = null, $excludeUserIds = [])
    {
        $devices = MobileDevice::where('tenant_id', $tenantId)
            ->where('notifications_enabled', true)
            ->whereNotIn('user_id', $excludeUserIds)
            ->distinct('user_id')
            ->get();

        $userIds = $devices->pluck('user_id')->unique()->toArray();

        return $this->sendBulkNotifications($userIds, $tenantId, $title, $body, $type, $data);
    }

    public function sendAppointmentReminder($appointmentId)
    {
        // Get appointment and send reminder
        $appointment = \App\Models\Appointment::findOrFail($appointmentId);
        $donor = $appointment->donor;
        
        $devices = MobileDevice::where('user_id', $donor->user_id)
            ->where('notifications_enabled', true)
            ->get();

        $results = [];

        foreach ($devices as $device) {
            $notification = PushNotification::create([
                'tenant_id' => $device->tenant_id,
                'user_id' => $device->user_id,
                'mobile_device_id' => $device->id,
                'title' => 'Upcoming Appointment',
                'body' => 'You have a blood donation appointment scheduled for ' . $appointment->scheduled_at->format('M d, h:i A'),
                'notification_type' => 'appointment_reminder',
                'action_url' => '/appointments/' . $appointmentId,
                'data' => ['appointment_id' => $appointmentId],
                'status' => 'pending',
            ]);

            $results[] = $notification->id;
        }

        // Dispatch send jobs
        foreach ($results as $notificationId) {
            dispatch(new \App\Jobs\SendPushNotification($notificationId));
        }

        return $results;
    }

    public function sendEligibilityUpdate($donorId, $isEligible)
    {
        $donor = \App\Models\Donor::findOrFail($donorId);
        $devices = MobileDevice::where('user_id', $donor->user_id)
            ->where('notifications_enabled', true)
            ->get();

        $message = $isEligible 
            ? 'Good news! You are eligible to donate blood.' 
            : 'You are currently not eligible to donate. Please try again later.';

        $results = [];

        foreach ($devices as $device) {
            $notification = PushNotification::create([
                'tenant_id' => $device->tenant_id,
                'user_id' => $device->user_id,
                'mobile_device_id' => $device->id,
                'title' => 'Eligibility Status Update',
                'body' => $message,
                'notification_type' => 'eligibility_update',
                'action_url' => '/donors/' . $donorId . '/eligibility',
                'data' => ['donor_id' => $donorId, 'eligible' => $isEligible],
                'status' => 'pending',
            ]);

            $results[] = $notification->id;
        }

        foreach ($results as $notificationId) {
            dispatch(new \App\Jobs\SendPushNotification($notificationId));
        }

        return $results;
    }

    public function sendViaPushService($notification)
    {
        $device = $notification->mobileDevice;

        try {
            if ($device->device_type === 'ios' && $device->apns_token) {
                $this->sendViaAPNS($notification, $device);
            } elseif ($device->device_type === 'android' && $device->fcm_token) {
                $this->sendViaFCM($notification, $device);
            } else {
                throw new \Exception('No valid push token for device');
            }

            $notification->markAsSent();
            return true;
        } catch (\Exception $e) {
            $notification->markAsFailed($e->getMessage());
            return false;
        }
    }

    private function sendViaFCM($notification, $device)
    {
        $fcmKey = config('services.firebase.server_key');

        $response = Http::withHeaders([
            'Authorization' => 'key=' . $fcmKey,
            'Content-Type' => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', [
            'to' => $device->fcm_token,
            'notification' => [
                'title' => $notification->title,
                'body' => $notification->body,
                'click_action' => $notification->action_url,
            ],
            'data' => $notification->data ?? [],
        ]);

        if (!$response->successful()) {
            throw new \Exception('FCM error: ' . $response->body());
        }
    }

    private function sendViaAPNS($notification, $device)
    {
        // Use Apple Push Notification Service
        $apnsKey = config('services.apple.apns_key');
        $apnsTeamId = config('services.apple.team_id');
        $apnsKeyId = config('services.apple.key_id');
        $appBundleId = config('services.apple.app_bundle_id');

        // This is a simplified example. In production, use a library like jwt
        $response = Http::withHeaders([
            'apns-topic' => $appBundleId,
            'apns-priority' => '10',
        ])->post('https://api.push.apple.com/3/device/' . $device->apns_token, [
            'aps' => [
                'alert' => [
                    'title' => $notification->title,
                    'body' => $notification->body,
                ],
                'badge' => 1,
                'sound' => 'default',
            ],
            'data' => $notification->data ?? [],
        ]);

        if (!$response->successful()) {
            throw new \Exception('APNS error: ' . $response->body());
        }
    }

    public function markAsRead($notificationId)
    {
        $notification = PushNotification::findOrFail($notificationId);
        $notification->markAsRead();

        return $notification;
    }

    public function getUserNotifications($userId, $limit = 50)
    {
        return PushNotification::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($limit);
    }

    public function getUnreadCount($userId)
    {
        return PushNotification::where('user_id', $userId)
            ->where('status', '!=', 'read')
            ->count();
    }

    public function deleteOldNotifications($days = 30)
    {
        $deleted = PushNotification::where('created_at', '<', now()->subDays($days))->delete();

        return $deleted;
    }

    public function getNotificationStats($tenantId)
    {
        $notifications = PushNotification::where('tenant_id', $tenantId)->get();

        return [
            'total' => $notifications->count(),
            'sent' => $notifications->where('status', 'sent')->count(),
            'read' => $notifications->where('status', 'read')->count(),
            'failed' => $notifications->where('status', 'failed')->count(),
            'pending' => $notifications->where('status', 'pending')->count(),
        ];
    }
}
