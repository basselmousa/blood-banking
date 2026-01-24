<?php

namespace App\Models\Traits;

use App\Models\Tenant;

trait BelongsToTenant
{
    /**
     * Boot the trait.
     */
    public static function bootBelongsToTenant()
    {
        // Automatically set tenant_id when creating a model
        static::creating(function ($model) {
            if (!$model->tenant_id && auth()->check()) {
                $model->tenant_id = auth()->user()->tenant_id;
            }
        });

        // Automatically filter by tenant when querying
        static::addGlobalScope('tenant', function ($query) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $query->where('tenant_id', auth()->user()->tenant_id);
            }
        });
    }

    /**
     * Get the tenant that owns the model.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Check if model belongs to the given tenant.
     */
    public function belongsToTenant(Tenant $tenant)
    {
        return $this->tenant_id === $tenant->id;
    }
}
