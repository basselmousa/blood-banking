<?php

namespace App\Http\Controllers\API\SaaS;

use App\Http\Controllers\Controller;
use App\Models\BiometricAuth;
use App\Services\BiometricAuthService;
use Illuminate\Http\Request;

class BiometricAuthController extends Controller
{
    protected $biometricAuthService;

    public function __construct(BiometricAuthService $biometricAuthService)
    {
        $this->middleware('auth:api');
        $this->biometricAuthService = $biometricAuthService;
    }

    /**
     * Register biometric authentication
     * POST /api/v1/saas/biometric-auth/register
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string|exists:mobile_devices,device_id',
            'auth_type' => 'required|in:fingerprint,face_recognition,iris',
            'biometric_data' => 'required|string', // Base64 encoded biometric data
        ]);

        $result = $this->biometricAuthService->registerBiometric(
            auth()->id(),
            $validated['device_id'],
            $validated['auth_type'],
            $validated['biometric_data']
        );

        return response()->json([
            'success' => $result['success'],
            'message' => 'Biometric registered successfully',
            'biometric' => $result,
        ], 201);
    }

    /**
     * Authenticate with biometric
     * POST /api/v1/saas/biometric-auth/authenticate
     */
    public function authenticate(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string',
            'auth_type' => 'required|in:fingerprint,face_recognition,iris',
        ]);

        $result = $this->biometricAuthService->authenticateWithBiometric(
            auth()->id(),
            $validated['device_id'],
            $validated['auth_type']
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Biometric authentication successful',
            'biometric_id' => $result['biometric_id'],
        ]);
    }

    /**
     * Get biometrics for current user
     * GET /api/v1/saas/biometric-auth
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'nullable|string',
        ]);

        $biometrics = $this->biometricAuthService->getBiometricsForUser(
            auth()->id(),
            $validated['device_id'] ?? null
        );

        return response()->json([
            'success' => true,
            'count' => $biometrics->count(),
            'biometrics' => $biometrics,
        ]);
    }

    /**
     * Get a specific biometric
     * GET /api/v1/saas/biometric-auth/{id}
     */
    public function show(BiometricAuth $biometricAuth)
    {
        $this->authorize('view', $biometricAuth);

        return response()->json([
            'success' => true,
            'biometric' => $biometricAuth,
        ]);
    }

    /**
     * Disable biometric authentication
     * POST /api/v1/saas/biometric-auth/{id}/disable
     */
    public function disable(BiometricAuth $biometricAuth)
    {
        $this->authorize('update', $biometricAuth);

        $biometric = $this->biometricAuthService->disableBiometric($biometricAuth->id);

        return response()->json([
            'success' => true,
            'message' => 'Biometric disabled',
            'biometric' => $biometric,
        ]);
    }

    /**
     * Set as primary biometric
     * POST /api/v1/saas/biometric-auth/{id}/set-primary
     */
    public function setPrimary(BiometricAuth $biometricAuth)
    {
        $this->authorize('update', $biometricAuth);

        $biometric = $this->biometricAuthService->setPrimaryBiometric($biometricAuth->id);

        return response()->json([
            'success' => true,
            'message' => 'Biometric set as primary',
            'biometric' => $biometric,
        ]);
    }

    /**
     * Record failed authentication attempt
     * POST /api/v1/saas/biometric-auth/{id}/record-failure
     */
    public function recordFailure(BiometricAuth $biometricAuth)
    {
        $this->authorize('update', $biometricAuth);

        $result = $this->biometricAuthService->recordFailedAttempt($biometricAuth->id);

        return response()->json([
            'success' => true,
            'message' => 'Failed attempt recorded',
            'biometric' => $result,
        ]);
    }

    /**
     * Check biometric availability
     * GET /api/v1/saas/biometric-auth/verify
     */
    public function verify(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string',
            'auth_type' => 'required|in:fingerprint,face_recognition,iris',
        ]);

        $result = $this->biometricAuthService->verifyBiometricAvailability(
            auth()->id(),
            $validated['device_id'],
            $validated['auth_type']
        );

        return response()->json([
            'success' => true,
            'available' => $result['available'],
            'message' => $result['reason'] ?? 'Biometric available',
            'biometric' => $result,
        ]);
    }

    /**
     * Unlock biometric (after lockout)
     * POST /api/v1/saas/biometric-auth/{id}/unlock
     */
    public function unlock(BiometricAuth $biometricAuth)
    {
        $this->authorize('update', $biometricAuth);

        $biometric = $this->biometricAuthService->unlockBiometric($biometricAuth->id);

        return response()->json([
            'success' => true,
            'message' => 'Biometric unlocked',
            'biometric' => $biometric,
        ]);
    }

    /**
     * Delete biometric authentication
     * DELETE /api/v1/saas/biometric-auth/{id}
     */
    public function destroy(BiometricAuth $biometricAuth)
    {
        $this->authorize('delete', $biometricAuth);

        $this->biometricAuthService->deleteBiometric($biometricAuth->id);

        return response()->json([
            'success' => true,
            'message' => 'Biometric deleted',
        ]);
    }

    /**
     * Get biometric statistics
     * GET /api/v1/saas/biometric-auth/stats
     */
    public function stats(Request $request)
    {
        $stats = $this->biometricAuthService->getBiometricStats(auth()->user()->tenant_id);

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }
}
