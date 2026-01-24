<?php

namespace App\Http\Controllers\API\SaaS;

use App\Http\Controllers\Controller;
use App\Models\MobileDevice;
use App\Services\MobileDeviceService;
use Illuminate\Http\Request;

class MobileDeviceController extends Controller
{
    protected $mobileDeviceService;

    public function __construct(MobileDeviceService $mobileDeviceService)
    {
        $this->middleware('auth:api');
        $this->mobileDeviceService = $mobileDeviceService;
    }

    /**
     * Register a new mobile device
     * POST /api/v1/saas/mobile-devices/register
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string|unique:mobile_devices,device_id',
            'device_type' => 'required|in:ios,android,web',
            'os_version' => 'required|string',
            'app_version' => 'required|string',
            'fcm_token' => 'nullable|string',
            'apns_token' => 'nullable|string',
        ]);

        $device = $this->mobileDeviceService->registerDevice(
            auth()->id(),
            auth()->user()->tenant_id,
            $validated
        );

        return response()->json([
            'success' => true,
            'message' => 'Device registered successfully',
            'device' => $device,
        ], 201);
    }

    /**
     * Update device tokens
     * PUT /api/v1/saas/mobile-devices/{id}/tokens
     */
    public function updateTokens(Request $request, MobileDevice $mobileDevice)
    {
        $this->authorize('update', $mobileDevice);

        $validated = $request->validate([
            'fcm_token' => 'nullable|string',
            'apns_token' => 'nullable|string',
        ]);

        $device = $this->mobileDeviceService->updateDeviceTokens(
            $mobileDevice->id,
            $validated['fcm_token'] ?? null,
            $validated['apns_token'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Device tokens updated',
            'device' => $device,
        ]);
    }

    /**
     * Update device information
     * PUT /api/v1/saas/mobile-devices/{id}
     */
    public function update(Request $request, MobileDevice $mobileDevice)
    {
        $this->authorize('update', $mobileDevice);

        $validated = $request->validate([
            'os_version' => 'nullable|string',
            'app_version' => 'nullable|string',
            'device_name' => 'nullable|string',
        ]);

        $device = $this->mobileDeviceService->updateDeviceInfo($mobileDevice->id, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Device updated successfully',
            'device' => $device,
        ]);
    }

    /**
     * Get all devices for current user
     * GET /api/v1/saas/mobile-devices
     */
    public function index(Request $request)
    {
        $devices = $this->mobileDeviceService->getDevicesForUser(auth()->id());

        return response()->json([
            'success' => true,
            'count' => $devices->count(),
            'devices' => $devices,
        ]);
    }

    /**
     * Get a specific device
     * GET /api/v1/saas/mobile-devices/{id}
     */
    public function show(MobileDevice $mobileDevice)
    {
        $this->authorize('view', $mobileDevice);

        return response()->json([
            'success' => true,
            'device' => $mobileDevice,
        ]);
    }

    /**
     * Mark device as active
     * POST /api/v1/saas/mobile-devices/{id}/mark-active
     */
    public function markActive(MobileDevice $mobileDevice)
    {
        $this->authorize('update', $mobileDevice);

        $device = $this->mobileDeviceService->markDeviceActive($mobileDevice->id);

        return response()->json([
            'success' => true,
            'message' => 'Device marked as active',
            'device' => $device,
        ]);
    }

    /**
     * Mark device as synced
     * POST /api/v1/saas/mobile-devices/{id}/mark-synced
     */
    public function markSynced(MobileDevice $mobileDevice)
    {
        $this->authorize('update', $mobileDevice);

        $device = $this->mobileDeviceService->markDeviceSynced($mobileDevice->id);

        return response()->json([
            'success' => true,
            'message' => 'Device marked as synced',
            'device' => $device,
        ]);
    }

    /**
     * Toggle notifications for device
     * POST /api/v1/saas/mobile-devices/{id}/toggle-notifications
     */
    public function toggleNotifications(Request $request, MobileDevice $mobileDevice)
    {
        $this->authorize('update', $mobileDevice);

        $validated = $request->validate([
            'enabled' => 'required|boolean',
        ]);

        $device = $this->mobileDeviceService->toggleNotifications(
            $mobileDevice->id,
            $validated['enabled']
        );

        return response()->json([
            'success' => true,
            'message' => 'Notifications ' . ($validated['enabled'] ? 'enabled' : 'disabled'),
            'device' => $device,
        ]);
    }

    /**
     * Toggle biometric authentication for device
     * POST /api/v1/saas/mobile-devices/{id}/toggle-biometric
     */
    public function toggleBiometric(Request $request, MobileDevice $mobileDevice)
    {
        $this->authorize('update', $mobileDevice);

        $validated = $request->validate([
            'enabled' => 'required|boolean',
        ]);

        $device = $this->mobileDeviceService->toggleBiometric(
            $mobileDevice->id,
            $validated['enabled']
        );

        return response()->json([
            'success' => true,
            'message' => 'Biometric authentication ' . ($validated['enabled'] ? 'enabled' : 'disabled'),
            'device' => $device,
        ]);
    }

    /**
     * Get active devices for tenant
     * GET /api/v1/saas/mobile-devices/active
     */
    public function getActive(Request $request)
    {
        $devices = $this->mobileDeviceService->getActiveDevices(
            auth()->user()->tenant_id,
            $request->input('minutes', 30)
        );

        return response()->json([
            'success' => true,
            'count' => $devices->count(),
            'devices' => $devices,
        ]);
    }

    /**
     * Unregister a device
     * DELETE /api/v1/saas/mobile-devices/{id}
     */
    public function destroy(MobileDevice $mobileDevice)
    {
        $this->authorize('delete', $mobileDevice);

        $this->mobileDeviceService->unregisterDevice($mobileDevice->id);

        return response()->json([
            'success' => true,
            'message' => 'Device unregistered successfully',
        ]);
    }

    /**
     * Get device statistics
     * GET /api/v1/saas/mobile-devices/stats
     */
    public function stats(Request $request)
    {
        $stats = $this->mobileDeviceService->getDeviceStats(auth()->user()->tenant_id);

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }
}
