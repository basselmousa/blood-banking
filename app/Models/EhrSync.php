<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EhrSync extends Model
{
    use \App\Traits\BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'integration_id',
        'entity_type',
        'entity_id',
        'external_id',
        'direction',
        'status',
        'external_data',
        'local_data',
        'error_message',
        'synced_at',
    ];

    protected $casts = [
        'external_data' => 'json',
        'local_data' => 'json',
        'synced_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function integration(): BelongsTo
    {
        return $this->belongsTo(Integration::class);
    }

    public function scopeByEntityType($query, $type)
    {
        return $query->where('entity_type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSynced($query)
    {
        return $query->where('status', 'synced');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function markSynced($externalId = null)
    {
        $this->update([
            'status' => 'synced',
            'external_id' => $externalId,
            'synced_at' => now(),
            'error_message' => null,
        ]);
    }

    public function markFailed($error = null)
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $error,
        ]);
    }

    public function markConflict($localData, $externalData)
    {
        $this->update([
            'status' => 'conflict',
            'local_data' => $localData,
            'external_data' => $externalData,
        ]);
    }

    public function resolveConflict($preferLocal = true)
    {
        if ($preferLocal) {
            $this->markSynced();
        } else {
            $this->update(['status' => 'synced']);
        }
    }
}
