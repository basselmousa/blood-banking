<?php

namespace App\Http\Controllers;

use App\Models\EhrSync;
use App\Services\EhrSyncService;
use Illuminate\Http\Request;

class EhrSyncController extends Controller
{
    protected $ehrSyncService;

    public function __construct(EhrSyncService $ehrSyncService)
    {
        $this->ehrSyncService = $ehrSyncService;
    }

    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $status = $request->query('status');
        $entityType = $request->query('entity_type');

        $query = EhrSync::where('tenant_id', $tenantId);

        if ($status) {
            $query->where('status', $status);
        }

        if ($entityType) {
            $query->where('entity_type', $entityType);
        }

        $syncs = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'data' => $syncs,
            'total' => $syncs->count(),
        ]);
    }

    public function show(EhrSync $sync)
    {
        $this->authorize('view', $sync);

        return response()->json($sync);
    }

    public function initializeSync(Request $request)
    {
        $validated = $request->validate([
            'integration_id' => 'required|exists:integrations,id',
            'entity_type' => 'required|in:donor,donation,appointment',
            'entity_id' => 'required|integer',
            'direction' => 'nullable|in:to_ehr,from_ehr,bidirectional',
        ]);

        $tenantId = auth()->user()->tenant_id;

        try {
            $sync = $this->ehrSyncService->initializeSync(
                $tenantId,
                $validated['integration_id'],
                $validated['entity_type'],
                $validated['entity_id'],
                $validated['direction'] ?? 'bidirectional'
            );

            return response()->json([
                'message' => 'Sync initiated',
                'data' => $sync,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to initialize sync',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function resolveConflict(Request $request, EhrSync $sync)
    {
        $this->authorize('update', $sync);

        $validated = $request->validate([
            'prefer_local' => 'required|boolean',
        ]);

        try {
            $sync = $this->ehrSyncService->resolveConflict($sync->id, $validated['prefer_local']);

            return response()->json([
                'message' => 'Conflict resolved',
                'data' => $sync,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to resolve conflict',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function resyncFailed(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        try {
            $count = $this->ehrSyncService->resyncFailed($tenantId);

            return response()->json([
                'message' => "Retrying $count failed syncs",
                'count' => $count,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retry syncs',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function statistics(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $stats = $this->ehrSyncService->getSyncStatistics($tenantId);

        return response()->json($stats);
    }

    public function history(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $status = $request->query('status');

        $history = $this->ehrSyncService->getSyncHistory($tenantId, $status);

        return response()->json([
            'data' => $history,
            'total' => $history->count(),
        ]);
    }
}
