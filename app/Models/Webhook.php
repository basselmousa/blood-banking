<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Webhook extends Model
{
    use \App\Traits\BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'event_type',
        'url',
        'description',
        'secret',
        'filters',
        'headers',
        'retry_count',
        'retry_delay',
        'is_active',
        'last_triggered_at',
        'total_deliveries',
        'failed_deliveries',
    ];

    protected $casts = [
        'filters' => 'json',
        'headers' => 'json',
        'is_active' => 'boolean',
        'last_triggered_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByEventType($query, $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    public function scopeFailedDeliveries($query)
    {
        return $query->where('failed_deliveries', '>', 0);
    }

    public function canDeliver($data)
    {
        if (!$this->filters) {
            return true;
        }

        foreach ($this->filters as $field => $value) {
            if (!isset($data[$field]) || $data[$field] !== $value) {
                return false;
            }
        }

        return true;
    }

    public function getSignature($payload)
    {
        if (!$this->secret) {
            return null;
        }

        return hash_hmac('sha256', $payload, $this->secret);
    }

    public function incrementDeliveries($success = true)
    {
        if ($success) {
            $this->increment('total_deliveries');
        } else {
            $this->increment('failed_deliveries');
        }
        $this->update(['last_triggered_at' => now()]);
    }
}
