<?php

namespace App\Http\Controllers\API\SaaS;

use App\Http\Controllers\Controller;
use App\Models\QrCodeScan;
use App\Services\QrCodeScanService;
use Illuminate\Http\Request;

class QrCodeScanController extends Controller
{
    protected $qrCodeScanService;

    public function __construct(QrCodeScanService $qrCodeScanService)
    {
        $this->middleware('auth:api');
        $this->qrCodeScanService = $qrCodeScanService;
    }

    /**
     * Record a QR code scan
     * POST /api/v1/saas/qr-scans
     */
    public function scan(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string|exists:mobile_devices,device_id',
            'qr_code_value' => 'required|string',
            'scan_metadata' => 'nullable|array',
        ]);

        $result = $this->qrCodeScanService->recordScan(
            auth()->id(),
            $validated['device_id'],
            $validated['qr_code_value'],
            $validated['scan_metadata'] ?? []
        );

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'] ?? 'QR code scanned',
            'scan' => $result,
        ], $result['success'] ? 201 : 400);
    }

    /**
     * Bulk record QR code scans
     * POST /api/v1/saas/qr-scans/bulk
     */
    public function bulkScan(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string|exists:mobile_devices,device_id',
            'qr_codes' => 'required|array|min:1',
            'qr_codes.*' => 'string',
            'scan_metadata' => 'nullable|array',
        ]);

        $result = $this->qrCodeScanService->bulkRecordScans(
            auth()->id(),
            $validated['device_id'],
            $validated['qr_codes'],
            $validated['scan_metadata'] ?? []
        );

        return response()->json([
            'success' => true,
            'message' => 'QR codes scanned',
            'result' => $result,
        ], 201);
    }

    /**
     * Get scan history for device
     * GET /api/v1/saas/qr-scans
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string',
            'entity_type' => 'nullable|string',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $scans = $this->qrCodeScanService->getScanHistory(
            $validated['device_id'],
            $validated['entity_type'] ?? null,
            $validated['limit'] ?? 100
        );

        return response()->json([
            'success' => true,
            'count' => $scans->count(),
            'scans' => $scans,
        ]);
    }

    /**
     * Get a specific scan
     * GET /api/v1/saas/qr-scans/{id}
     */
    public function show(QrCodeScan $qrCodeScan)
    {
        $this->authorize('view', $qrCodeScan);

        return response()->json([
            'success' => true,
            'scan' => $qrCodeScan,
        ]);
    }

    /**
     * Get scans for a specific entity
     * GET /api/v1/saas/qr-scans/entity/{entityType}/{entityId}
     */
    public function getForEntity(Request $request, $entityType, $entityId)
    {
        $validated = $request->validate([
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $scans = $this->qrCodeScanService->getScansForEntity(
            $entityType,
            $entityId,
            $validated['limit'] ?? 50
        );

        return response()->json([
            'success' => true,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'count' => $scans->count(),
            'scans' => $scans,
        ]);
    }

    /**
     * Get recent scans
     * GET /api/v1/saas/qr-scans/recent
     */
    public function getRecent(Request $request)
    {
        $validated = $request->validate([
            'hours' => 'nullable|integer|min:1|max:168',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $scans = $this->qrCodeScanService->getRecentScans(
            auth()->user()->tenant_id,
            $validated['hours'] ?? 24,
            $validated['limit'] ?? 100
        );

        return response()->json([
            'success' => true,
            'count' => $scans->count(),
            'scans' => $scans,
        ]);
    }

    /**
     * Get failed scans
     * GET /api/v1/saas/qr-scans/failed
     */
    public function getFailed(Request $request)
    {
        $validated = $request->validate([
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $scans = $this->qrCodeScanService->getFailedScans(
            auth()->user()->tenant_id,
            $validated['limit'] ?? 50
        );

        return response()->json([
            'success' => true,
            'count' => $scans->count(),
            'scans' => $scans,
        ]);
    }

    /**
     * Get scan statistics
     * GET /api/v1/saas/qr-scans/stats
     */
    public function stats(Request $request)
    {
        $stats = $this->qrCodeScanService->getScanStats(auth()->user()->tenant_id);

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    /**
     * Validate QR code
     * POST /api/v1/saas/qr-scans/validate
     */
    public function validate(Request $request)
    {
        $validated = $request->validate([
            'qr_code_value' => 'required|string',
            'expected_entity_type' => 'required|string',
            'expected_entity_id' => 'required|integer',
        ]);

        $result = $this->qrCodeScanService->validateQrForEntity(
            $validated['qr_code_value'],
            $validated['expected_entity_type'],
            $validated['expected_entity_id']
        );

        return response()->json([
            'success' => $result['valid'],
            'message' => $result['message'] ?? 'Valid QR code',
            'result' => $result,
        ]);
    }

    /**
     * Generate QR code value
     * POST /api/v1/saas/qr-scans/generate
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'entity_type' => 'required|string',
            'entity_id' => 'required|integer',
        ]);

        $qrValue = $this->qrCodeScanService->generateQrCode(
            $validated['entity_type'],
            $validated['entity_id']
        );

        return response()->json([
            'success' => true,
            'qr_code_value' => $qrValue,
            'entity_type' => $validated['entity_type'],
            'entity_id' => $validated['entity_id'],
        ]);
    }

    /**
     * Get user scan history
     * GET /api/v1/saas/qr-scans/user
     */
    public function userHistory(Request $request)
    {
        $validated = $request->validate([
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $scans = $this->qrCodeScanService->getScansForUser(
            auth()->id(),
            $validated['limit'] ?? 100
        );

        return response()->json([
            'success' => true,
            'count' => $scans->count(),
            'scans' => $scans,
        ]);
    }

    /**
     * Delete a scan record
     * DELETE /api/v1/saas/qr-scans/{id}
     */
    public function destroy(QrCodeScan $qrCodeScan)
    {
        $this->authorize('delete', $qrCodeScan);

        $qrCodeScan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Scan record deleted',
        ]);
    }
}
