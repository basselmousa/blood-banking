<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'description',
    ];

    /**
     * Get the tenant that owns the role.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the permissions for the role.
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    /**
     * Get the users with this role.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles')
            ->withPivot('tenant_id')
            ->withTimestamps();
    }

    /**
     * Grant a permission to the role.
     */
    public function grantPermission($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::whereSlug($permission)->first();
        }

        if ($permission) {
            $this->permissions()->syncWithoutDetaching([$permission->id]);
        }

        return $this;
    }

    /**
     * Revoke a permission from the role.
     */
    public function revokePermission($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::whereSlug($permission)->first();
        }

        if ($permission) {
            $this->permissions()->detach($permission->id);
        }

        return $this;
    }

    /**
     * Check if role has a permission.
     */
    public function hasPermission($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::whereSlug($permission)->first();
        }

        return $this->permissions()->where('permissions.id', $permission->id)->exists();
    }
}
