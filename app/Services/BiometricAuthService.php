<?php

namespace App\Services;

use App\Models\BiometricAuth;
use App\Models\MobileDevice;
use Illuminate\Support\Str;

class BiometricAuthService
{
    public function registerBiometric($userId, $deviceId, $authType, $biometricData)
    {
        $device = MobileDevice::where('device_id', $deviceId)->firstOrFail();

        // In production, you would generate a secure token from biometric data
        $token = Str::random(64);

        $biometric = BiometricAuth::create([
            'user_id' => $userId,
            'mobile_device_id' => $device->id,
            'auth_type' => $authType,
            'encrypted_token' => encrypt($token),
            'is_enabled' => true,
            'is_primary' => !BiometricAuth::where('user_id', $userId)
                ->where('mobile_device_id', $device->id)
                ->exists(),
        ]);

        $device->update(['biometric_enabled' => true]);

        return [
            'success' => true,
            'biometric_id' => $biometric->id,
            'auth_type' => $authType,
        ];
    }

    public function authenticateWithBiometric($userId, $deviceId, $authType)
    {
        $device = MobileDevice::where('device_id', $deviceId)->firstOrFail();

        $biometric = BiometricAuth::where('user_id', $userId)
            ->where('mobile_device_id', $device->id)
            ->where('auth_type', $authType)
            ->where('is_enabled', true)
            ->first();

        if (!$biometric) {
            return [
                'success' => false,
                'message' => 'Biometric authentication not configured',
            ];
        }

        if ($biometric->isLocked()) {
            return [
                'success' => false,
                'message' => 'Too many failed attempts. Try again in 15 minutes.',
                'locked_until' => $biometric->locked_until,
            ];
        }

        // In production, verify biometric data against encrypted token
        // For now, we'll assume successful authentication
        $biometric->recordSuccessfulAuth();

        return [
            'success' => true,
            'biometric_id' => $biometric->id,
            'message' => 'Biometric authentication successful',
        ];
    }

    public function recordFailedAttempt($biometricId)
    {
        $biometric = BiometricAuth::findOrFail($biometricId);
        $biometric->recordFailedAttempt();

        return [
            'locked' => $biometric->isLocked(),
            'failed_attempts' => $biometric->failed_attempts,
            'locked_until' => $biometric->locked_until,
        ];
    }

    public function getBiometricsForUser($userId, $deviceId = null)
    {
        $query = BiometricAuth::where('user_id', $userId)->where('is_enabled', true);

        if ($deviceId) {
            $device = MobileDevice::where('device_id', $deviceId)->firstOrFail();
            $query->where('mobile_device_id', $device->id);
        }

        return $query->get();
    }

    public function disableBiometric($biometricId)
    {
        $biometric = BiometricAuth::findOrFail($biometricId);
        $biometric->update(['is_enabled' => false]);

        // Check if any biometrics remain for this device
        $remaining = BiometricAuth::where('mobile_device_id', $biometric->mobile_device_id)
            ->where('is_enabled', true)
            ->exists();

        if (!$remaining) {
            $biometric->mobileDevice->update(['biometric_enabled' => false]);
        }

        return $biometric;
    }

    public function setPrimaryBiometric($biometricId)
    {
        $biometric = BiometricAuth::findOrFail($biometricId);
        $biometric->setPrimary();

        return $biometric;
    }

    public function deleteBiometric($biometricId)
    {
        $biometric = BiometricAuth::findOrFail($biometricId);
        $mobileDevice = $biometric->mobileDevice;

        $biometric->delete();

        // Check if any biometrics remain
        $remaining = BiometricAuth::where('mobile_device_id', $mobileDevice->id)
            ->where('is_enabled', true)
            ->exists();

        if (!$remaining) {
            $mobileDevice->update(['biometric_enabled' => false]);
        }

        return true;
    }

    public function unlockBiometric($biometricId)
    {
        $biometric = BiometricAuth::findOrFail($biometricId);
        $biometric->unlock();

        return $biometric;
    }

    public function verifyBiometricAvailability($userId, $deviceId, $authType)
    {
        $device = MobileDevice::where('device_id', $deviceId)->first();

        if (!$device) {
            return [
                'available' => false,
                'reason' => 'Device not registered',
            ];
        }

        $biometric = BiometricAuth::where('user_id', $userId)
            ->where('mobile_device_id', $device->id)
            ->where('auth_type', $authType)
            ->first();

        if (!$biometric) {
            return [
                'available' => false,
                'reason' => 'Biometric not configured for this type',
            ];
        }

        if (!$biometric->is_enabled) {
            return [
                'available' => false,
                'reason' => 'Biometric is disabled',
            ];
        }

        if ($biometric->isLocked()) {
            return [
                'available' => false,
                'reason' => 'Biometric is locked due to failed attempts',
                'locked_until' => $biometric->locked_until,
            ];
        }

        return [
            'available' => true,
            'biometric_id' => $biometric->id,
        ];
    }

    public function getBiometricStats($tenantId)
    {
        $biometrics = BiometricAuth::join('mobile_devices', 'biometric_auths.mobile_device_id', '=', 'mobile_devices.id')
            ->where('mobile_devices.tenant_id', $tenantId)
            ->get();

        return [
            'total' => $biometrics->count(),
            'enabled' => $biometrics->where('is_enabled', true)->count(),
            'fingerprint' => $biometrics->where('auth_type', 'fingerprint')->count(),
            'face_recognition' => $biometrics->where('auth_type', 'face_recognition')->count(),
            'iris' => $biometrics->where('auth_type', 'iris')->count(),
        ];
    }
}
