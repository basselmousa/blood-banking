<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Integration extends Model
{
    use \App\Traits\BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'type',
        'credentials',
        'config',
        'is_active',
        'last_sync_at',
        'sync_status',
        'error_message',
        'metadata',
    ];

    protected $casts = [
        'credentials' => 'encrypted:json',
        'config' => 'json',
        'metadata' => 'json',
        'is_active' => 'boolean',
        'last_sync_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByName($query, $name)
    {
        return $query->where('name', $name);
    }

    public function isHealthy()
    {
        return $this->is_active && $this->sync_status !== 'failed';
    }

    public function setSyncStatus($status, $errorMessage = null)
    {
        $this->update([
            'sync_status' => $status,
            'error_message' => $errorMessage,
            'last_sync_at' => $status === 'syncing' ? $this->last_sync_at : now(),
        ]);
    }

    public function getCredential($key)
    {
        return $this->credentials[$key] ?? null;
    }

    public function setCredential($key, $value)
    {
        $credentials = $this->credentials ?? [];
        $credentials[$key] = $value;
        $this->update(['credentials' => $credentials]);
    }
}
