<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MobileDevice extends Model
{
    use \App\Traits\BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'device_id',
        'device_type',
        'device_name',
        'os_version',
        'app_version',
        'fcm_token',
        'apns_token',
        'notifications_enabled',
        'biometric_enabled',
        'last_sync_at',
        'last_active_at',
    ];

    protected $casts = [
        'notifications_enabled' => 'boolean',
        'biometric_enabled' => 'boolean',
        'last_sync_at' => 'datetime',
        'last_active_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function offlineSyncs(): HasMany
    {
        return $this->hasMany(OfflineSync::class);
    }

    public function pushNotifications(): HasMany
    {
        return $this->hasMany(PushNotification::class);
    }

    public function biometricAuths(): HasMany
    {
        return $this->hasMany(BiometricAuth::class);
    }

    public function qrScans(): HasMany
    {
        return $this->hasMany(QrCodeScan::class);
    }

    public function markAsActive()
    {
        $this->update(['last_active_at' => now()]);
    }

    public function markAsSynced()
    {
        $this->update(['last_sync_at' => now()]);
    }

    public function updateTokens($fcmToken = null, $apnsToken = null)
    {
        $updates = [];
        if ($fcmToken) {
            $updates['fcm_token'] = $fcmToken;
        }
        if ($apnsToken) {
            $updates['apns_token'] = $apnsToken;
        }

        if (!empty($updates)) {
            $this->update($updates);
        }

        return $this;
    }

    public function scopeWithNotificationsEnabled($query)
    {
        return $query->where('notifications_enabled', true);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('device_type', $type);
    }

    public function scopeRecentlyActive($query, $minutes = 24 * 60)
    {
        return $query->where('last_active_at', '>=', now()->subMinutes($minutes));
    }
}
