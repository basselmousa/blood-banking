<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrCodeScan extends Model
{
    use \App\Traits\BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'mobile_device_id',
        'entity_type',
        'entity_id',
        'qr_code_value',
        'scan_status',
        'error_message',
        'scan_metadata',
    ];

    protected $casts = [
        'scan_metadata' => 'json',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mobileDevice(): BelongsTo
    {
        return $this->belongsTo(MobileDevice::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function getScannedEntity()
    {
        $modelClass = 'App\\Models\\' . ucfirst($this->entity_type);
        
        if (class_exists($modelClass)) {
            return $modelClass::find($this->entity_id);
        }

        return null;
    }

    public function scopeSuccess($query)
    {
        return $query->where('scan_status', 'success');
    }

    public function scopeFailed($query)
    {
        return $query->where('scan_status', 'failed');
    }

    public function scopeForEntity($query, $entityType, $entityId = null)
    {
        $query->where('entity_type', $entityType);
        
        if ($entityId) {
            $query->where('entity_id', $entityId);
        }

        return $query;
    }

    public function scopeRecentScans($query, $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    public const SCAN_STATUSES = [
        'success',
        'failed',
        'not_found',
        'invalid',
    ];
}
