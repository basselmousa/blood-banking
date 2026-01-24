<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookDelivery extends Model
{
    protected $fillable = [
        'webhook_id',
        'event_type',
        'payload',
        'http_status',
        'response_body',
        'attempt_number',
        'delivered_at',
        'next_retry_at',
        'status',
        'error_message',
    ];

    protected $casts = [
        'payload' => 'json',
        'delivered_at' => 'datetime',
        'next_retry_at' => 'datetime',
    ];

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class);
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending')
            ->whereNull('next_retry_at')
            ->orWhere('next_retry_at', '<=', now());
    }

    public function markDelivered($httpStatus, $response = null)
    {
        $this->update([
            'status' => 'delivered',
            'http_status' => $httpStatus,
            'response_body' => $response,
            'delivered_at' => now(),
        ]);

        $this->webhook->incrementDeliveries(true);
    }

    public function markFailed($httpStatus, $error = null)
    {
        $webhook = $this->webhook;
        $nextRetry = null;

        if ($this->attempt_number < $webhook->retry_count) {
            $nextRetry = now()->addSeconds($webhook->retry_delay);
        } else {
            $this->status = 'failed';
        }

        $this->update([
            'status' => $this->attempt_number >= $webhook->retry_count ? 'failed' : 'pending',
            'http_status' => $httpStatus,
            'response_body' => $error,
            'error_message' => $error,
            'next_retry_at' => $nextRetry,
            'attempt_number' => $this->attempt_number + 1,
        ]);

        if ($this->status === 'failed') {
            $webhook->incrementDeliveries(false);
        }
    }
}
