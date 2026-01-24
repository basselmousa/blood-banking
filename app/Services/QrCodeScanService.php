<?php

namespace App\Services;

use App\Models\QrCodeScan;
use App\Models\MobileDevice;
use Illuminate\Support\Str;

class QrCodeScanService
{
    public function recordScan($userId, $deviceId, $qrCodeValue, $scanMetadata = [])
    {
        $device = MobileDevice::where('device_id', $deviceId)->firstOrFail();

        // Parse QR code value to determine entity type and ID
        $parsedQr = $this->parseQrCode($qrCodeValue);

        if (!$parsedQr) {
            return [
                'success' => false,
                'scan_status' => 'invalid',
                'message' => 'Invalid QR code format',
            ];
        }

        // Verify entity exists
        $entity = $this->getEntity($parsedQr['entity_type'], $parsedQr['entity_id']);

        if (!$entity) {
            $scanStatus = 'not_found';
        } else {
            $scanStatus = 'success';
        }

        // Record the scan
        $scan = QrCodeScan::create([
            'user_id' => $userId,
            'mobile_device_id' => $device->id,
            'tenant_id' => $device->tenant_id,
            'entity_type' => $parsedQr['entity_type'],
            'entity_id' => $parsedQr['entity_id'],
            'qr_code_value' => $qrCodeValue,
            'scan_status' => $scanStatus,
            'scan_metadata' => array_merge($scanMetadata, [
                'timestamp' => now()->toIso8601String(),
                'device_id' => $device->device_id,
                'os' => $device->device_type,
            ]),
        ]);

        return [
            'success' => $scanStatus === 'success',
            'scan_id' => $scan->id,
            'scan_status' => $scanStatus,
            'entity_type' => $parsedQr['entity_type'],
            'entity_id' => $parsedQr['entity_id'],
            'entity' => $entity,
        ];
    }

    public function parseQrCode($qrCodeValue)
    {
        // QR code format: TYPE:ID or TYPE-ID
        // Examples: DONOR:123, PATIENT:456, APPOINTMENT:789

        if (strpos($qrCodeValue, ':') !== false) {
            list($type, $id) = explode(':', $qrCodeValue);
        } elseif (strpos($qrCodeValue, '-') !== false) {
            list($type, $id) = explode('-', $qrCodeValue);
        } else {
            return null;
        }

        $type = strtoupper(trim($type));
        $id = intval(trim($id));

        if (!in_array($type, ['DONOR', 'PATIENT', 'APPOINTMENT', 'CAMP', 'INVENTORY'])) {
            return null;
        }

        return [
            'entity_type' => $type,
            'entity_id' => $id,
        ];
    }

    public function getEntity($entityType, $entityId)
    {
        $modelMap = [
            'DONOR' => 'App\Models\Donor',
            'PATIENT' => 'App\Models\Patient',
            'APPOINTMENT' => 'App\Models\Appointment',
            'CAMP' => 'App\Models\Camp',
            'INVENTORY' => 'App\Models\Inventory',
        ];

        if (!isset($modelMap[$entityType])) {
            return null;
        }

        $modelClass = $modelMap[$entityType];

        return $modelClass::find($entityId);
    }

    public function getScanHistory($deviceId, $entityType = null, $limit = 100)
    {
        $device = MobileDevice::where('device_id', $deviceId)->firstOrFail();

        $query = QrCodeScan::where('mobile_device_id', $device->id)
            ->orderBy('created_at', 'desc');

        if ($entityType) {
            $query->where('entity_type', strtoupper($entityType));
        }

        return $query->limit($limit)->get();
    }

    public function getScansForEntity($entityType, $entityId, $limit = 50)
    {
        return QrCodeScan::where('entity_type', strtoupper($entityType))
            ->where('entity_id', $entityId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getRecentScans($tenantId, $hours = 24, $limit = 100)
    {
        return QrCodeScan::where('tenant_id', $tenantId)
            ->where('created_at', '>=', now()->subHours($hours))
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getFailedScans($tenantId, $limit = 50)
    {
        return QrCodeScan::where('tenant_id', $tenantId)
            ->where('scan_status', '!=', 'success')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getScanStats($tenantId)
    {
        $allScans = QrCodeScan::where('tenant_id', $tenantId)->get();

        return [
            'total_scans' => $allScans->count(),
            'successful_scans' => $allScans->where('scan_status', 'success')->count(),
            'failed_scans' => $allScans->where('scan_status', 'failed')->count(),
            'not_found_scans' => $allScans->where('scan_status', 'not_found')->count(),
            'invalid_scans' => $allScans->where('scan_status', 'invalid')->count(),
            'by_entity_type' => [
                'donors' => $allScans->where('entity_type', 'DONOR')->count(),
                'patients' => $allScans->where('entity_type', 'PATIENT')->count(),
                'appointments' => $allScans->where('entity_type', 'APPOINTMENT')->count(),
                'camps' => $allScans->where('entity_type', 'CAMP')->count(),
                'inventory' => $allScans->where('entity_type', 'INVENTORY')->count(),
            ],
        ];
    }

    public function generateQrCode($entityType, $entityId)
    {
        // Generate QR code value in standard format
        return sprintf('%s:%d', strtoupper($entityType), $entityId);
    }

    public function validateQrForEntity($qrCodeValue, $expectedEntityType, $expectedEntityId)
    {
        $parsed = $this->parseQrCode($qrCodeValue);

        if (!$parsed) {
            return [
                'valid' => false,
                'message' => 'Invalid QR code format',
            ];
        }

        if ($parsed['entity_type'] !== strtoupper($expectedEntityType)) {
            return [
                'valid' => false,
                'message' => 'QR code entity type mismatch',
            ];
        }

        if ($parsed['entity_id'] != $expectedEntityId) {
            return [
                'valid' => false,
                'message' => 'QR code entity ID mismatch',
            ];
        }

        $entity = $this->getEntity($parsed['entity_type'], $parsed['entity_id']);

        if (!$entity) {
            return [
                'valid' => false,
                'message' => 'Entity not found',
            ];
        }

        return [
            'valid' => true,
            'entity' => $entity,
        ];
    }

    public function getScansForUser($userId, $limit = 100)
    {
        return QrCodeScan::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getScansByStatus($tenantId, $scanStatus)
    {
        return QrCodeScan::where('tenant_id', $tenantId)
            ->where('scan_status', $scanStatus)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function bulkRecordScans($userId, $deviceId, $qrCodeValues, $scanMetadata = [])
    {
        $results = [];

        foreach ($qrCodeValues as $qrCodeValue) {
            $result = $this->recordScan($userId, $deviceId, $qrCodeValue, $scanMetadata);
            $results[] = $result;
        }

        return [
            'total' => count($results),
            'successful' => count(array_filter($results, fn($r) => $r['success'])),
            'failed' => count(array_filter($results, fn($r) => !$r['success'])),
            'scans' => $results,
        ];
    }
}
