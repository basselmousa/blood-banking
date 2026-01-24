<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfflineSync extends Model
{
    use \App\Traits\BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'mobile_device_id',
        'entity_type',
        'entity_id',
        'action',
        'data',
        'changes',
        'status',
        'error_message',
        'conflict_data',
        'synced_at',
        'retry_count',
        'last_retry_at',
    ];

    protected $casts = [
        'data' => 'json',
        'changes' => 'json',
        'conflict_data' => 'json',
        'synced_at' => 'datetime',
        'last_retry_at' => 'datetime',
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

    public function getEntity()
    {
        $modelClass = 'App\\Models\\' . ucfirst($this->entity_type);
        
        if (class_exists($modelClass)) {
            return $modelClass::find($this->entity_id);
        }

        return null;
    }

    public function markAsSynced()
    {
        $this->update([
            'status' => 'synced',
            'synced_at' => now(),
            'error_message' => null,
        ]);
    }

    public function markAsFailed($errorMessage)
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'retry_count' => $this->retry_count + 1,
            'last_retry_at' => now(),
        ]);
    }

    public function markAsConflict($conflictData)
    {
        $this->update([
            'status' => 'conflict',
            'conflict_data' => $conflictData,
        ]);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeConflicts($query)
    {
        return $query->where('status', 'conflict');
    }

    public function scopeForEntity($query, $entityType, $entityId)
    {
        return $query->where('entity_type', $entityType)->where('entity_id', $entityId);
    }

    public function shouldRetry()
    {
        return $this->retry_count < 5 && $this->status === 'failed';
    }
}
