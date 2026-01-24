<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiKey extends Model
{
    use \App\Traits\BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'name',
        'key',
        'secret',
        'permissions',
        'scopes',
        'rate_limit',
        'last_used_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'permissions' => 'json',
        'scopes' => 'json',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $hidden = ['key', 'secret'];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->key = hash('sha256', \Illuminate\Support\Str::random(32));
            if (!$model->secret) {
                $model->secret = hash('sha256', \Illuminate\Support\Str::random(32));
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function hasPermission($permission)
    {
        if (!$this->permissions) {
            return true;
        }

        return in_array($permission, $this->permissions);
    }

    public function hasScope($scope)
    {
        if (!$this->scopes) {
            return true;
        }

        return in_array($scope, $this->scopes);
    }

    public function recordUsage()
    {
        $this->update(['last_used_at' => now()]);
    }

    public function getDisplayKey()
    {
        return substr($this->key, 0, 10) . '...' . substr($this->key, -10);
    }
}
