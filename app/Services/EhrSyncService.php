<?php

namespace App\Services;

use App\Models\EhrSync;
use App\Models\Integration;

class EhrSyncService
{
    public function initializeSync($tenantId, $integrationId, $entityType, $entityId, $direction = 'bidirectional')
    {
        $sync = EhrSync::create([
            'tenant_id' => $tenantId,
            'integration_id' => $integrationId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'direction' => $direction,
            'status' => 'pending',
        ]);

        // Dispatch sync job
        \App\Jobs\SyncToEhr::dispatch($sync);

        return $sync;
    }

    public function syncToEhr(EhrSync $sync)
    {
        $integration = $sync->integration;

        if (!$integration->is_active) {
            $sync->markFailed('Integration is disabled');
            return $sync;
        }

        try {
            // Get local entity data
            $localData = $this->getLocalEntityData($sync->entity_type, $sync->entity_id);

            // Transform to EHR format
            $ehrData = $this->transformToEhrFormat($sync->entity_type, $localData);

            // Push to external system
            $externalId = $this->pushToExternalSystem($integration, $sync->entity_type, $ehrData);

            $sync->markSynced($externalId);
        } catch (\Exception $e) {
            $sync->markFailed($e->getMessage());
        }

        return $sync;
    }

    public function syncFromEhr(EhrSync $sync)
    {
        try {
            // Pull from external system
            $externalData = $this->pullFromExternalSystem(
                $sync->integration,
                $sync->entity_type,
                $sync->external_id
            );

            // Transform to local format
            $localData = $this->transformFromEhrFormat($sync->entity_type, $externalData);

            // Check for conflicts
            $currentData = $this->getLocalEntityData($sync->entity_type, $sync->entity_id);

            if ($this->hasConflict($currentData, $localData)) {
                $sync->markConflict($currentData, $externalData);
                return $sync;
            }

            // Update local entity
            $this->updateLocalEntity($sync->entity_type, $sync->entity_id, $localData);

            $sync->markSynced();
        } catch (\Exception $e) {
            $sync->markFailed($e->getMessage());
        }

        return $sync;
    }

    public function resyncFailed($tenantId)
    {
        $failedSyncs = EhrSync::where('tenant_id', $tenantId)
            ->where('status', 'failed')
            ->get();

        foreach ($failedSyncs as $sync) {
            if ($sync->direction === 'to_ehr') {
                \App\Jobs\SyncToEhr::dispatch($sync);
            } else {
                \App\Jobs\SyncFromEhr::dispatch($sync);
            }
        }

        return $failedSyncs->count();
    }

    public function resolveConflict($syncId, $preferLocal = true)
    {
        $sync = EhrSync::findOrFail($syncId);

        if ($sync->status !== 'conflict') {
            throw new \Exception('Sync is not in conflict state');
        }

        if ($preferLocal) {
            $this->syncToEhr($sync);
        } else {
            $this->syncFromEhr($sync);
        }

        return $sync;
    }

    private function getLocalEntityData($entityType, $entityId)
    {
        switch ($entityType) {
            case 'donor':
                return \App\Models\Donor::find($entityId);
            case 'donation':
                return \App\Models\Donation::find($entityId);
            case 'appointment':
                return \App\Models\Appointment::find($entityId);
            default:
                throw new \Exception("Unknown entity type: $entityType");
        }
    }

    private function transformToEhrFormat($entityType, $entity)
    {
        switch ($entityType) {
            case 'donor':
                return [
                    'mrn' => $entity->mrn ?? $entity->id,
                    'first_name' => $entity->first_name,
                    'last_name' => $entity->last_name,
                    'date_of_birth' => $entity->date_of_birth,
                    'blood_type' => $entity->blood_type,
                    'phone' => $entity->phone,
                    'email' => $entity->email,
                ];

            case 'donation':
                return [
                    'donation_id' => $entity->id,
                    'donor_mrn' => $entity->donor->mrn ?? $entity->donor->id,
                    'donation_date' => $entity->created_at,
                    'blood_type' => $entity->blood_type,
                    'quantity_ml' => $entity->quantity,
                ];

            case 'appointment':
                return [
                    'appointment_id' => $entity->id,
                    'donor_mrn' => $entity->donor->mrn ?? $entity->donor->id,
                    'scheduled_at' => $entity->scheduled_at,
                    'status' => $entity->status,
                ];

            default:
                return [];
        }
    }

    private function transformFromEhrFormat($entityType, $data)
    {
        return $data; // Implement provider-specific transformation
    }

    private function pushToExternalSystem(Integration $integration, $entityType, $data)
    {
        switch ($integration->name) {
            case 'epic':
                return $this->pushToEpic($integration, $entityType, $data);
            case 'cerner':
                return $this->pushToCerner($integration, $entityType, $data);
            default:
                throw new \Exception("Unsupported EHR system: {$integration->name}");
        }
    }

    private function pullFromExternalSystem(Integration $integration, $entityType, $externalId)
    {
        switch ($integration->name) {
            case 'epic':
                return $this->pullFromEpic($integration, $entityType, $externalId);
            case 'cerner':
                return $this->pullFromCerner($integration, $entityType, $externalId);
            default:
                throw new \Exception("Unsupported EHR system: {$integration->name}");
        }
    }

    private function pushToEpic($integration, $entityType, $data)
    {
        // Epic FHIR API integration
        // Implement actual API calls
        return 'epic_' . uniqid();
    }

    private function pullFromEpic($integration, $entityType, $externalId)
    {
        // Epic FHIR API integration
        return [];
    }

    private function pushToCerner($integration, $entityType, $data)
    {
        // Cerner FHIR API integration
        return 'cerner_' . uniqid();
    }

    private function pullFromCerner($integration, $entityType, $externalId)
    {
        // Cerner FHIR API integration
        return [];
    }

    private function hasConflict($localData, $externalData)
    {
        // Compare timestamps or version numbers
        return false;
    }

    private function updateLocalEntity($entityType, $entityId, $data)
    {
        // Update local entity with external data
        switch ($entityType) {
            case 'donor':
                \App\Models\Donor::find($entityId)->update($data);
                break;
            case 'donation':
                \App\Models\Donation::find($entityId)->update($data);
                break;
            case 'appointment':
                \App\Models\Appointment::find($entityId)->update($data);
                break;
        }
    }

    public function getSyncHistory($tenantId, $status = null)
    {
        $query = EhrSync::where('tenant_id', $tenantId);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function getSyncStatistics($tenantId)
    {
        $total = EhrSync::where('tenant_id', $tenantId)->count();
        $synced = EhrSync::where('tenant_id', $tenantId)->where('status', 'synced')->count();
        $failed = EhrSync::where('tenant_id', $tenantId)->where('status', 'failed')->count();
        $conflicts = EhrSync::where('tenant_id', $tenantId)->where('status', 'conflict')->count();

        return [
            'total' => $total,
            'synced' => $synced,
            'failed' => $failed,
            'conflicts' => $conflicts,
            'sync_rate' => $total > 0 ? round(($synced / $total) * 100, 2) : 0,
        ];
    }
}
