<?php

namespace App\Services;

use App\Models\MobileDevice;
use App\Models\User;
use Illuminate\Support\Str;

class MobileDeviceService
{
    public function registerDevice($userId, $tenantId, array $deviceData)
    {
        $device = MobileDevice::updateOrCreate(
            [
                'user_id' => $userId,
                'device_id' => $deviceData['device_id'],
            ],
            [
                'tenant_id' => $tenantId,
                'device_type' => $deviceData['device_type'] ?? 'android',
                'device_name' => $deviceData['device_name'] ?? null,
                'os_version' => $deviceData['os_version'] ?? null,
                'app_version' => $deviceData['app_version'] ?? null,
                'fcm_token' => $deviceData['fcm_token'] ?? null,
                'apns_token' => $deviceData['apns_token'] ?? null,
                'last_active_at' => now(),
            ]
        );

        return $device;
    }

    public function updateDeviceTokens($deviceId, $fcmToken = null, $apnsToken = null)
    {
        $device = MobileDevice::where('device_id', $deviceId)->firstOrFail();
        $device->updateTokens($fcmToken, $apnsToken);

        return $device;
    }

    public function updateDeviceInfo($deviceId, array $deviceData)
    {
        $device = MobileDevice::where('device_id', $deviceId)->firstOrFail();

        $updates = [];
        if (isset($deviceData['device_name'])) {
            $updates['device_name'] = $deviceData['device_name'];
        }
        if (isset($deviceData['os_version'])) {
            $updates['os_version'] = $deviceData['os_version'];
        }
        if (isset($deviceData['app_version'])) {
            $updates['app_version'] = $deviceData['app_version'];
        }

        if (!empty($updates)) {
            $device->update($updates);
        }

        return $device;
    }

    public function getDevicesForUser($userId)
    {
        return MobileDevice::where('user_id', $userId)
            ->orderBy('last_active_at', 'desc')
            ->get();
    }

    public function getDevicesForTenant($tenantId)
    {
        return MobileDevice::where('tenant_id', $tenantId)
            ->orderBy('last_active_at', 'desc')
            ->get();
    }

    public function markDeviceActive($deviceId)
    {
        $device = MobileDevice::where('device_id', $deviceId)->firstOrFail();
        $device->markAsActive();

        return $device;
    }

    public function markDeviceSynced($deviceId)
    {
        $device = MobileDevice::where('device_id', $deviceId)->firstOrFail();
        $device->markAsSynced();

        return $device;
    }

    public function toggleNotifications($deviceId, $enabled)
    {
        $device = MobileDevice::where('device_id', $deviceId)->firstOrFail();
        $device->update(['notifications_enabled' => $enabled]);

        return $device;
    }

    public function toggleBiometric($deviceId, $enabled)
    {
        $device = MobileDevice::where('device_id', $deviceId)->firstOrFail();
        $device->update(['biometric_enabled' => $enabled]);

        return $device;
    }

    public function unregisterDevice($deviceId)
    {
        return MobileDevice::where('device_id', $deviceId)->delete();
    }

    public function getActiveDevices($tenantId, $hoursThreshold = 24)
    {
        return MobileDevice::where('tenant_id', $tenantId)
            ->recentlyActive($hoursThreshold)
            ->get();
    }

    public function cleanupInactiveDevices($hoursThreshold = 90 * 24)
    {
        $inactiveDevices = MobileDevice::where('last_active_at', '<', now()->subHours($hoursThreshold))
            ->get();

        foreach ($inactiveDevices as $device) {
            $device->delete();
        }

        return count($inactiveDevices);
    }

    public function getDeviceStats($tenantId)
    {
        $devices = MobileDevice::where('tenant_id', $tenantId)->get();

        return [
            'total_devices' => $devices->count(),
            'ios_devices' => $devices->where('device_type', 'ios')->count(),
            'android_devices' => $devices->where('device_type', 'android')->count(),
            'web_devices' => $devices->where('device_type', 'web')->count(),
            'notifications_enabled' => $devices->where('notifications_enabled', true)->count(),
            'biometric_enabled' => $devices->where('biometric_enabled', true)->count(),
            'active_last_24h' => $devices->where('last_active_at', '>=', now()->subHours(24))->count(),
        ];
    }
}
