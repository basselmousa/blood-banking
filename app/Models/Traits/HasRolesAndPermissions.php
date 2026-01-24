<?php

namespace App\Models\Traits;

use App\Models\Permission;
use App\Models\Role;

trait HasRolesAndPermissions
{
    /**
     * Get all roles for the user in a specific tenant.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withPivot('tenant_id')
            ->withTimestamps();
    }

    /**
     * Check if user has a role.
     */
    public function hasRole($role, $tenantId = null)
    {
        if (is_string($role)) {
            $role = Role::whereSlug($role)->first();
        }

        $tenantId = $tenantId ?? auth()->user()->tenant_id ?? $this->tenant_id;

        return $this->roles()
            ->where('roles.id', $role->id)
            ->wherePivot('tenant_id', $tenantId)
            ->exists();
    }

    /**
     * Check if user has any of the given roles.
     */
    public function hasAnyRole($roles, $tenantId = null)
    {
        if (is_string($roles)) {
            $roles = [$roles];
        }

        foreach ($roles as $role) {
            if ($this->hasRole($role, $tenantId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has all of the given roles.
     */
    public function hasAllRoles($roles, $tenantId = null)
    {
        if (is_string($roles)) {
            $roles = [$roles];
        }

        foreach ($roles as $role) {
            if (!$this->hasRole($role, $tenantId)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if user has a permission.
     */
    public function hasPermission($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::whereSlug($permission)->first();
        }

        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permission) {
                $query->where('permissions.id', $permission->id);
            })
            ->exists();
    }

    /**
     * Check if user has any of the given permissions.
     */
    public function hasAnyPermission($permissions)
    {
        if (is_string($permissions)) {
            $permissions = [$permissions];
        }

        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Assign a role to the user.
     */
    public function assignRole($role, $tenantId = null)
    {
        if (is_string($role)) {
            $role = Role::whereSlug($role)->first();
        }

        $tenantId = $tenantId ?? auth()->user()->tenant_id ?? $this->tenant_id;

        $this->roles()->attach($role->id, ['tenant_id' => $tenantId]);

        return $this;
    }

    /**
     * Remove a role from the user.
     */
    public function removeRole($role, $tenantId = null)
    {
        if (is_string($role)) {
            $role = Role::whereSlug($role)->first();
        }

        $this->roles()->detach($role->id);

        return $this;
    }
}
