<?php

namespace App\Http\Controllers\API\SaaS;

use App\Http\Controllers\Controller;
use App\Models\OfflineSync;
use App\Services\OfflineSyncService;
use Illuminate\Http\Request;

class OfflineSyncController extends Controller
{
    protected $offlineSyncService;

    public function __construct(OfflineSyncService $offlineSyncService)
    {
        $this->middleware('auth:api');
        $this->offlineSyncService = $offlineSyncService;
    }

    /**
     * Record offline changes
     * POST /api/v1/saas/offline-syncs/record
     */
    public function record(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string',
            'changes' => 'required|array',
            'changes.*.entity_type' => 'required|string',
            'changes.*.entity_id' => 'required|integer',
            'changes.*.action' => 'required|in:create,update,delete',
            'changes.*.data' => 'required|array',
        ]);

        $results = [];

        foreach ($validated['changes'] as $change) {
            $result = $this->offlineSyncService->recordOfflineChange(
                auth()->id(),
                $validated['device_id'],
                $change['entity_type'],
                $change['entity_id'],
                $change['action'],
                $change['data'],
                $change
            );

            $results[] = $result;
        }

        return response()->json([
            'success' => true,
            'message' => 'Offline changes recorded',
            'recorded' => count($results),
            'changes' => $results,
        ], 201);
    }

    /**
     * Get pending syncs for device
     * GET /api/v1/saas/offline-syncs/pending
     */
    public function getPending(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string',
        ]);

        $syncs = $this->offlineSyncService->getPendingSyncs(
            auth()->id(),
            $validated['device_id']
        );

        return response()->json([
            'success' => true,
            'count' => $syncs->count(),
            'syncs' => $syncs,
        ]);
    }

    /**
     * Get sync status
     * GET /api/v1/saas/offline-syncs/status
     */
    public function getStatus(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string',
        ]);

        $status = $this->offlineSyncService->getSyncStatus(
            auth()->id(),
            $validated['device_id']
        );

        return response()->json([
            'success' => true,
            'status' => $status,
        ]);
    }

    /**
     * Process all pending syncs
     * POST /api/v1/saas/offline-syncs/process
     */
    public function process(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string',
        ]);

        $result = $this->offlineSyncService->processPendingSyncs(
            auth()->id(),
            $validated['device_id']
        );

        return response()->json([
            'success' => true,
            'message' => 'Syncs processed',
            'result' => $result,
        ]);
    }

    /**
     * Get a specific sync
     * GET /api/v1/saas/offline-syncs/{id}
     */
    public function show(OfflineSync $offlineSync)
    {
        $this->authorize('view', $offlineSync);

        return response()->json([
            'success' => true,
            'sync' => $offlineSync,
        ]);
    }

    /**
     * Resolve a sync conflict
     * POST /api/v1/saas/offline-syncs/{id}/resolve
     */
    public function resolveConflict(Request $request, OfflineSync $offlineSync)
    {
        $this->authorize('update', $offlineSync);

        $validated = $request->validate([
            'resolution' => 'required|in:server,mobile',
            'server_data' => 'nullable|array',
        ]);

        $result = $this->offlineSyncService->resolveConflict(
            $offlineSync->id,
            $validated['resolution'],
            $validated['server_data'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Conflict resolved',
            'sync' => $result,
        ]);
    }

    /**
     * Retry failed syncs
     * POST /api/v1/saas/offline-syncs/retry
     */
    public function retryFailed(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string',
        ]);

        $result = $this->offlineSyncService->retryFailedSyncs(
            auth()->id(),
            $validated['device_id']
        );

        return response()->json([
            'success' => true,
            'message' => 'Failed syncs retried',
            'result' => $result,
        ]);
    }

    /**
     * Mark sync as synced
     * POST /api/v1/saas/offline-syncs/{id}/mark-synced
     */
    public function markSynced(OfflineSync $offlineSync)
    {
        $this->authorize('update', $offlineSync);

        $sync = $this->offlineSyncService->markSyncComplete($offlineSync->id);

        return response()->json([
            'success' => true,
            'message' => 'Sync marked as synced',
            'sync' => $sync,
        ]);
    }

    /**
     * Get sync history for device
     * GET /api/v1/saas/offline-syncs/history
     */
    public function getHistory(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $syncs = $this->offlineSyncService->getSyncHistory(
            auth()->id(),
            $validated['device_id'],
            $validated['limit'] ?? 50
        );

        return response()->json([
            'success' => true,
            'count' => $syncs->count(),
            'syncs' => $syncs,
        ]);
    }

    /**
     * Get conflicts for device
     * GET /api/v1/saas/offline-syncs/conflicts
     */
    public function getConflicts(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string',
        ]);

        $conflicts = $this->offlineSyncService->getConflicts(
            auth()->id(),
            $validated['device_id']
        );

        return response()->json([
            'success' => true,
            'count' => $conflicts->count(),
            'conflicts' => $conflicts,
        ]);
    }

    /**
     * Delete a sync record
     * DELETE /api/v1/saas/offline-syncs/{id}
     */
    public function destroy(OfflineSync $offlineSync)
    {
        $this->authorize('delete', $offlineSync);

        $offlineSync->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sync record deleted',
        ]);
    }
}
