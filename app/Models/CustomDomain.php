<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomDomain extends Model
{
    use \App\Traits\BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'domain',
        'base_domain',
        'is_primary',
        'is_verified',
        'verification_token',
        'verified_at',
        'ssl_certificate',
        'ssl_key',
        'ssl_expires_at',
        'is_active',
        'dns_records',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'dns_records' => 'json',
        'verified_at' => 'datetime',
        'ssl_expires_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function verify($token = null)
    {
        if ($token && $this->verification_token !== $token) {
            return false;
        }

        $this->update([
            'is_verified' => true,
            'verified_at' => now(),
            'verification_token' => null,
        ]);

        return true;
    }

    public function getVerificationDnsRecords()
    {
        return [
            'type' => 'CNAME',
            'name' => '_acme-challenge.' . $this->domain,
            'value' => $this->verification_token,
        ];
    }

    public function isSslExpiringSoon($days = 30)
    {
        return $this->ssl_expires_at && $this->ssl_expires_at->lessThanOrEqualTo(now()->addDays($days));
    }
}
