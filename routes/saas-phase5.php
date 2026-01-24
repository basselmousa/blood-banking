<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\SaaS\MobileDeviceController;
use App\Http\Controllers\API\SaaS\OfflineSyncController;
use App\Http\Controllers\API\SaaS\PushNotificationController;
use App\Http\Controllers\API\SaaS\BiometricAuthController;
use App\Http\Controllers\API\SaaS\QrCodeScanController;

Route::middleware('auth:api')->group(function () {
    // ================== MOBILE DEVICE MANAGEMENT ==================
    Route::prefix('mobile-devices')->group(function () {
        // Device registration and management
        Route::post('register', [MobileDeviceController::class, 'register']);
        Route::get('/', [MobileDeviceController::class, 'index']);
        Route::get('/{mobileDevice}', [MobileDeviceController::class, 'show']);
        Route::put('/{mobileDevice}', [MobileDeviceController::class, 'update']);
        Route::delete('/{mobileDevice}', [MobileDeviceController::class, 'destroy']);

        // Token management
        Route::put('/{mobileDevice}/tokens', [MobileDeviceController::class, 'updateTokens']);

        // Device status
        Route::post('/{mobileDevice}/mark-active', [MobileDeviceController::class, 'markActive']);
        Route::post('/{mobileDevice}/mark-synced', [MobileDeviceController::class, 'markSynced']);

        // Notification preferences
        Route::post('/{mobileDevice}/toggle-notifications', [MobileDeviceController::class, 'toggleNotifications']);
        Route::post('/{mobileDevice}/toggle-biometric', [MobileDeviceController::class, 'toggleBiometric']);

        // Statistics and monitoring
        Route::get('/active', [MobileDeviceController::class, 'getActive']);
        Route::get('/stats', [MobileDeviceController::class, 'stats']);
    });

    // ================== OFFLINE SYNC MANAGEMENT ==================
    Route::prefix('offline-syncs')->group(function () {
        // Core sync operations
        Route::post('record', [OfflineSyncController::class, 'record']);
        Route::post('process', [OfflineSyncController::class, 'process']);
        Route::post('retry', [OfflineSyncController::class, 'retryFailed']);

        // Sync status and history
        Route::get('pending', [OfflineSyncController::class, 'getPending']);
        Route::get('status', [OfflineSyncController::class, 'getStatus']);
        Route::get('history', [OfflineSyncController::class, 'getHistory']);
        Route::get('conflicts', [OfflineSyncController::class, 'getConflicts']);

        // Individual sync operations
        Route::get('/{offlineSync}', [OfflineSyncController::class, 'show']);
        Route::post('/{offlineSync}/resolve', [OfflineSyncController::class, 'resolveConflict']);
        Route::post('/{offlineSync}/mark-synced', [OfflineSyncController::class, 'markSynced']);
        Route::delete('/{offlineSync}', [OfflineSyncController::class, 'destroy']);
    });

    // ================== PUSH NOTIFICATIONS ==================
    Route::prefix('push-notifications')->group(function () {
        // Send notifications
        Route::post('send', [PushNotificationController::class, 'send']);
        Route::post('send-bulk', [PushNotificationController::class, 'sendBulk']);
        Route::post('send-tenant', [PushNotificationController::class, 'sendToTenant']);

        // Specialized notifications
        Route::post('appointment-reminder', [PushNotificationController::class, 'sendAppointmentReminder']);
        Route::post('eligibility-update', [PushNotificationController::class, 'sendEligibilityUpdate']);

        // User notifications
        Route::get('/', [PushNotificationController::class, 'index']);
        Route::get('/unread/count', [PushNotificationController::class, 'unreadCount']);
        Route::get('/{pushNotification}', [PushNotificationController::class, 'show']);
        Route::post('/{pushNotification}/mark-read', [PushNotificationController::class, 'markRead']);
        Route::delete('/{pushNotification}', [PushNotificationController::class, 'destroy']);

        // Statistics
        Route::get('/stats', [PushNotificationController::class, 'stats']);
    });

    // ================== BIOMETRIC AUTHENTICATION ==================
    Route::prefix('biometric-auth')->group(function () {
        // Biometric setup
        Route::post('register', [BiometricAuthController::class, 'register']);
        Route::get('/', [BiometricAuthController::class, 'index']);
        Route::get('/{biometricAuth}', [BiometricAuthController::class, 'show']);

        // Biometric authentication
        Route::post('authenticate', [BiometricAuthController::class, 'authenticate']);
        Route::get('verify', [BiometricAuthController::class, 'verify']);

        // Biometric management
        Route::post('/{biometricAuth}/set-primary', [BiometricAuthController::class, 'setPrimary']);
        Route::post('/{biometricAuth}/disable', [BiometricAuthController::class, 'disable']);
        Route::post('/{biometricAuth}/unlock', [BiometricAuthController::class, 'unlock']);
        Route::post('/{biometricAuth}/record-failure', [BiometricAuthController::class, 'recordFailure']);
        Route::delete('/{biometricAuth}', [BiometricAuthController::class, 'destroy']);

        // Statistics
        Route::get('/stats', [BiometricAuthController::class, 'stats']);
    });

    // ================== QR CODE SCANNING ==================
    Route::prefix('qr-scans')->group(function () {
        // Scan operations
        Route::post('/', [QrCodeScanController::class, 'scan']);
        Route::post('bulk', [QrCodeScanController::class, 'bulkScan']);

        // Scan history
        Route::get('/', [QrCodeScanController::class, 'index']);
        Route::get('recent', [QrCodeScanController::class, 'getRecent']);
        Route::get('failed', [QrCodeScanController::class, 'getFailed']);
        Route::get('user', [QrCodeScanController::class, 'userHistory']);
        Route::get('/{qrCodeScan}', [QrCodeScanController::class, 'show']);
        Route::delete('/{qrCodeScan}', [QrCodeScanController::class, 'destroy']);

        // Entity-specific scans
        Route::get('entity/{entityType}/{entityId}', [QrCodeScanController::class, 'getForEntity']);

        // QR code utilities
        Route::post('validate', [QrCodeScanController::class, 'validate']);
        Route::post('generate', [QrCodeScanController::class, 'generate']);

        // Statistics
        Route::get('/stats', [QrCodeScanController::class, 'stats']);
    });
});
