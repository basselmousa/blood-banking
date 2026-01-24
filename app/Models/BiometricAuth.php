<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Encryption\Encrypter;

class BiometricAuth extends Model
{
    use \App\Traits\BelongsToTenant;

    protected $fillable = [
        'user_id',
        'mobile_device_id',
        'auth_type',
        'encrypted_token',
        'is_enabled',
        'is_primary',
        'failed_attempts',
        'last_used_at',
        'locked_until',
        'metadata',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'is_primary' => 'boolean',
        'last_used_at' => 'datetime',
        'locked_until' => 'datetime',
        'metadata' => 'json',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mobileDevice(): BelongsTo
    {
        return $this->belongsTo(MobileDevice::class);
    }

    public function getDecryptedToken()
    {
        return decrypt($this->encrypted_token);
    }

    public function setEncryptedToken($token)
    {
        $this->encrypted_token = encrypt($token);
    }

    public function recordFailedAttempt()
    {
        $failedAttempts = $this->failed_attempts + 1;
        
        $updates = [
            'failed_attempts' => $failedAttempts,
        ];

        if ($failedAttempts >= 5) {
            $updates['locked_until'] = now()->addMinutes(15);
        }

        $this->update($updates);
    }

    public function recordSuccessfulAuth()
    {
        $this->update([
            'failed_attempts' => 0,
            'last_used_at' => now(),
            'locked_until' => null,
        ]);
    }

    public function isLocked()
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    public function unlock()
    {
        $this->update([
            'failed_attempts' => 0,
            'locked_until' => null,
        ]);
    }

    public function setPrimary()
    {
        BiometricAuth::where('user_id', $this->user_id)
            ->where('mobile_device_id', $this->mobile_device_id)
            ->update(['is_primary' => false]);

        $this->update(['is_primary' => true]);
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('auth_type', $type);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public const AUTH_TYPES = [
        'fingerprint',
        'face_recognition',
        'iris',
    ];
}
