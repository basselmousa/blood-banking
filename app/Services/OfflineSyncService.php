<?php

namespace App\Services;

use App\Models\OfflineSync;
use App\Models\MobileDevice;
use Illuminate\Support\Arr;

class OfflineSyncService
{
    public function recordOfflineChange($userId, $deviceId, $entityType, $entityId, $action, $data, $changes = null)
    {
        $device = MobileDevice::where('device_id', $deviceId)->firstOrFail();

        return OfflineSync::create([
            'tenant_id' => $device->tenant_id,
            'user_id' => $userId,
            'mobile_device_id' => $device->id,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'data' => $data,
            'changes' => $changes,
            'status' => 'pending',
        ]);
    }

    public function getPendingSyncs($deviceId)
    {
        $device = MobileDevice::where('device_id', $deviceId)->firstOrFail();

        return OfflineSync::where('mobile_device_id', $device->id)
            ->where('status', 'pending')
            ->orderBy('created_at')
            ->get();
    }

    public function getSyncStatus($deviceId)
    {
        $device = MobileDevice::where('device_id', $deviceId)->firstOrFail();

        $syncs = OfflineSync::where('mobile_device_id', $device->id)->get();

        return [
            'total' => $syncs->count(),
            'pending' => $syncs->where('status', 'pending')->count(),
            'synced' => $syncs->where('status', 'synced')->count(),
            'failed' => $syncs->where('status', 'failed')->count(),
            'conflicts' => $syncs->where('status', 'conflict')->count(),
        ];
    }

    public function markSyncComplete($syncId)
    {
        $sync = OfflineSync::findOrFail($syncId);
        $sync->markAsSynced();

        return $sync;
    }

    public function markSyncFailed($syncId, $errorMessage)
    {
        $sync = OfflineSync::findOrFail($syncId);
        $sync->markAsFailed($errorMessage);

        return $sync;
    }

    public function resolveConflict($syncId, $resolution = 'server')
    {
        $sync = OfflineSync::findOrFail($syncId);

        if ($resolution === 'server') {
            // Keep server version, discard mobile changes
            $sync->update(['status' => 'synced']);
        } elseif ($resolution === 'mobile') {
            // Apply mobile changes, overwrite server
            $sync->update(['status' => 'synced']);
            // Process the update on server side
        }

        return $sync;
    }

    public function processPendingSyncs($deviceId)
    {
        $device = MobileDevice::where('device_id', $deviceId)->firstOrFail();
        $pendingSyncs = $this->getPendingSyncs($deviceId);

        $results = [
            'successful' => [],
            'failed' => [],
            'conflicts' => [],
        ];

        foreach ($pendingSyncs as $sync) {
            try {
                $this->applySync($sync);
                $sync->markAsSynced();
                $results['successful'][] = $sync->id;
            } catch (\Exception $e) {
                $sync->markAsFailed($e->getMessage());
                $results['failed'][] = [
                    'sync_id' => $sync->id,
                    'error' => $e->getMessage(),
                ];
            }
        }

        $device->markAsSynced();

        return $results;
    }

    private function applySync($sync)
    {
        $entity = $sync->getEntity();

        switch ($sync->action) {
            case 'create':
                // Entity data should contain all needed fields
                if (!$entity) {
                    $modelClass = 'App\\Models\\' . ucfirst($sync->entity_type);
                    $modelClass::create($sync->data);
                }
                break;

            case 'update':
                if ($entity) {
                    // Check if there are conflicts
                    $serverData = $entity->only(array_keys($sync->changes ?? []));
                    if ($serverData != ($sync->data['original'] ?? [])) {
                        // Conflict detected
                        throw new \Exception('Conflict: Server version differs from mobile version');
                    }
                    $entity->update($sync->changes ?? []);
                }
                break;

            case 'delete':
                if ($entity) {
                    $entity->delete();
                }
                break;
        }
    }

    public function retryFailedSyncs($deviceId)
    {
        $device = MobileDevice::where('device_id', $deviceId)->firstOrFail();

        $failedSyncs = OfflineSync::where('mobile_device_id', $device->id)
            ->where('status', 'failed')
            ->where('retry_count', '<', 5)
            ->get();

        $results = [
            'retried' => 0,
            'still_failed' => 0,
        ];

        foreach ($failedSyncs as $sync) {
            try {
                $this->applySync($sync);
                $sync->markAsSynced();
                $results['retried']++;
            } catch (\Exception $e) {
                $sync->markAsFailed($e->getMessage());
                $results['still_failed']++;
            }
        }

        return $results;
    }

    public function clearOldSyncs($days = 90)
    {
        $deleted = OfflineSync::where('synced_at', '<', now()->subDays($days))
            ->where('status', 'synced')
            ->delete();

        return $deleted;
    }

    public function getSyncHistory($deviceId, $limit = 100)
    {
        $device = MobileDevice::where('device_id', $deviceId)->firstOrFail();

        return OfflineSync::where('mobile_device_id', $device->id)
            ->orderBy('created_at', 'desc')
            ->paginate($limit);
    }
}
