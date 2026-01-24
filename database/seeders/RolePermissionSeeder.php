<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create permissions
        $permissions = [
            // Admin permissions
            ['name' => 'Manage Tenants', 'slug' => 'manage_tenants', 'category' => 'admin'],
            ['name' => 'Manage Users', 'slug' => 'manage_users', 'category' => 'admin'],
            ['name' => 'Manage Roles', 'slug' => 'manage_roles', 'category' => 'admin'],
            ['name' => 'Manage Permissions', 'slug' => 'manage_permissions', 'category' => 'admin'],
            ['name' => 'View Reports', 'slug' => 'view_reports', 'category' => 'admin'],

            // Donation permissions
            ['name' => 'Create Donation', 'slug' => 'create_donation', 'category' => 'donation'],
            ['name' => 'View Donation', 'slug' => 'view_donation', 'category' => 'donation'],
            ['name' => 'Edit Donation', 'slug' => 'edit_donation', 'category' => 'donation'],
            ['name' => 'Delete Donation', 'slug' => 'delete_donation', 'category' => 'donation'],
            ['name' => 'Record Donation', 'slug' => 'record_donation', 'category' => 'donation'],

            // Inventory permissions
            ['name' => 'Manage Inventory', 'slug' => 'manage_inventory', 'category' => 'inventory'],
            ['name' => 'View Inventory', 'slug' => 'view_inventory', 'category' => 'inventory'],

            // Patient permissions
            ['name' => 'Create Patient', 'slug' => 'create_patient', 'category' => 'patient'],
            ['name' => 'View Patient', 'slug' => 'view_patient', 'category' => 'patient'],
            ['name' => 'Edit Patient', 'slug' => 'edit_patient', 'category' => 'patient'],

            // Donor permissions
            ['name' => 'Create Donor', 'slug' => 'create_donor', 'category' => 'donor'],
            ['name' => 'View Donor', 'slug' => 'view_donor', 'category' => 'donor'],
            ['name' => 'Edit Donor', 'slug' => 'edit_donor', 'category' => 'donor'],
            ['name' => 'Delete Donor', 'slug' => 'delete_donor', 'category' => 'donor'],

            // Billing permissions
            ['name' => 'Manage Billing', 'slug' => 'manage_billing', 'category' => 'billing'],
            ['name' => 'View Billing', 'slug' => 'view_billing', 'category' => 'billing'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['slug' => $permission['slug']],
                ['name' => $permission['name'], 'category' => $permission['category']]
            );
        }

        // Create default roles (these are global, not tenant-specific)
        $roles = [
            ['name' => 'Super Admin', 'slug' => 'super_admin', 'description' => 'Full system access'],
            ['name' => 'Tenant Admin', 'slug' => 'tenant_admin', 'description' => 'Full access to tenant'],
            ['name' => 'Manager', 'slug' => 'manager', 'description' => 'Can manage donations and inventory'],
            ['name' => 'Staff', 'slug' => 'staff', 'description' => 'Can record donations'],
            ['name' => 'Viewer', 'slug' => 'viewer', 'description' => 'Can only view data'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['slug' => $role['slug']],
                ['name' => $role['name'], 'description' => $role['description']]
            );
        }

        // Assign permissions to roles
        $superAdminRole = Role::where('slug', 'super_admin')->first();
        $tenantAdminRole = Role::where('slug', 'tenant_admin')->first();
        $managerRole = Role::where('slug', 'manager')->first();
        $staffRole = Role::where('slug', 'staff')->first();
        $viewerRole = Role::where('slug', 'viewer')->first();

        // Super Admin gets all permissions
        $allPermissions = Permission::pluck('id')->toArray();
        $superAdminRole->permissions()->sync($allPermissions);

        // Tenant Admin gets all permissions except super admin
        $tenantAdminPermissions = Permission::whereNotIn('slug', ['manage_tenants'])->pluck('id')->toArray();
        $tenantAdminRole->permissions()->sync($tenantAdminPermissions);

        // Manager
        $managerPermissions = Permission::whereIn('slug', [
            'create_donation',
            'view_donation',
            'edit_donation',
            'record_donation',
            'manage_inventory',
            'view_inventory',
            'create_donor',
            'view_donor',
            'edit_donor',
            'create_patient',
            'view_patient',
            'edit_patient',
            'view_billing',
        ])->pluck('id')->toArray();
        $managerRole->permissions()->sync($managerPermissions);

        // Staff
        $staffPermissions = Permission::whereIn('slug', [
            'create_donation',
            'view_donation',
            'record_donation',
            'view_inventory',
            'create_donor',
            'view_donor',
        ])->pluck('id')->toArray();
        $staffRole->permissions()->sync($staffPermissions);

        // Viewer
        $viewerPermissions = Permission::whereIn('slug', [
            'view_donation',
            'view_inventory',
            'view_donor',
            'view_patient',
            'view_billing',
        ])->pluck('id')->toArray();
        $viewerRole->permissions()->sync($viewerPermissions);
    }
}
