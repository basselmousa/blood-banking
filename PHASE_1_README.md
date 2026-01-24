# Phase 1: Multi-Tenancy & Core SaaS Implementation

**Branch:** `gemini/features/SaaS/phase1`

**Duration:** 2 months estimated

**Status:** 🚀 In Development

---

## Overview

Phase 1 is the foundation of the SaaS platform. It introduces:

1. **Multi-Tenancy Architecture** - Support multiple organizations
2. **Subscription Management** - Plan-based feature access
3. **Role-Based Access Control (RBAC)** - Granular permissions
4. **Organization Management** - Tenant administration
5. **Plan-Based Feature Gating** - Limit features by plan tier

---

## 📋 What's Implemented

### Database Migrations

✅ **Tenants Table** (`2026_01_24_000001_create_tenants_table.php`)
- Multi-tenant organization storage
- Plan tracking (starter, professional, enterprise)
- Features JSON for plan-based feature gating
- Settings JSON for organization configuration

✅ **Subscriptions Table** (`2026_01_24_000002_create_subscriptions_table.php`)
- Track subscription status
- Stripe integration ready
- Trial period support
- Plan upgrade/downgrade history

✅ **Roles & Permissions Tables** (`2026_01_24_000003_create_roles_and_permissions_table.php`)
- Global permissions catalog
- Tenant-specific roles
- Role-permission relationships
- User-role pivot with tenant scope

✅ **Tenant ID Migration** (`2026_01_24_000004_add_tenant_id_to_existing_tables.php`)
- Add `tenant_id` to users, donors, patients
- Automatic tenant isolation
- Foreign key constraints

### Models

✅ **Tenant Model** (`app/Models/Tenant.php`)
- Manage organization data
- Plan configuration
- Feature checking
- Subscription relationship

✅ **Subscription Model** (`app/Models/Subscription.php`)
- Track subscription status
- Trial and cancellation support
- Plan upgrades
- Stripe integration ready

✅ **Role Model** (`app/Models/Role.php`)
- Tenant-scoped roles
- Permission management
- User-role assignment

✅ **Permission Model** (`app/Models/Permission.php`)
- Global permissions catalog
- Permission categories
- Role relationships

### Traits

✅ **BelongsToTenant** (`app/Models/Traits/BelongsToTenant.php`)
- Automatic tenant_id assignment
- Global scope filtering by tenant
- Tenant relationship

✅ **HasRolesAndPermissions** (`app/Models/Traits/HasRolesAndPermissions.php`)
- User-role management
- Permission checking
- Role assignment/removal
- Role and permission queries

### Middleware

✅ **ResolveTenant** (`app/Http/Middleware/ResolveTenant.php`)
- Resolve current tenant from authenticated user
- Check subscription active status
- Set tenant in request/session

✅ **CheckFeatureAvailable** (`app/Http/Middleware/CheckFeatureAvailable.php`)
- Verify feature availability for plan
- Redirect if feature not available
- Plan-based access control

### Services

✅ **TenantService** (`app/Services/TenantService.php`)
- Create and manage tenants
- Plan-based feature assignment
- Usage statistics
- Limit checking (users, donors)

✅ **SubscriptionService** (`app/Services/SubscriptionService.php`)
- Create and manage subscriptions
- Plan upgrades/downgrades
- Cancellation and resumption
- Subscription details

### Controllers

✅ **TenantController** (`app/Http/Controllers/TenantController.php`)
- List, create, view, edit tenants
- Activate/deactivate tenants
- Delete tenants
- Admin management

✅ **SubscriptionController** (`app/Http/Controllers/SubscriptionController.php`)
- View subscription details
- Upgrade/downgrade plans
- Cancel/resume subscriptions
- Plan information display

### Routes

✅ **SaaS Phase 1 Routes** (`routes/saas-phase1.php`)
- Tenant management endpoints
- Subscription management endpoints
- Protected by auth and authorization middleware

### Seeders

✅ **RolePermissionSeeder** (`database/seeders/RolePermissionSeeder.php`)
- 20+ permissions across 6 categories
- 5 default roles (Super Admin, Tenant Admin, Manager, Staff, Viewer)
- Role-permission mapping

---

## 🚀 Getting Started

### 1. Run Migrations

```bash
php artisan migrate
```

This will:
- Create `tenants` table
- Create `subscriptions` table
- Create `roles`, `permissions`, `role_permissions`, `user_roles` tables
- Add `tenant_id` to existing tables

### 2. Seed Roles and Permissions

```bash
php artisan db:seed --class=RolePermissionSeeder
```

### 3. Register Routes

Add to `routes/web.php`:

```php
// Include Phase 1 SaaS routes
require base_path('routes/saas-phase1.php');
```

### 4. Register Middleware

Add to `app/Http/Kernel.php`:

```php
protected $routeMiddleware = [
    // ... existing middleware
    'resolve_tenant' => \App\Http\Middleware\ResolveTenant::class,
    'check_feature' => \App\Http\Middleware\CheckFeatureAvailable::class,
];
```

### 5. Update Models

The `User` model has already been updated with:
- `BelongsToTenant` trait
- `HasRolesAndPermissions` trait
- `tenant_id` in fillable array

You can now update other models (Donor, Patient) to use `BelongsToTenant` trait.

---

## 📐 Architecture

### Multi-Tenancy Flow

```
User Login
    ↓
ResolveTenant Middleware
    ↓
Load User's Tenant
    ↓
Set Tenant Context
    ↓
Global Scope: Filter by tenant_id
    ↓
Automatic Tenant Isolation
```

### Subscription Flow

```
User Subscribes
    ↓
Create Subscription
    ↓
Update Tenant Plan
    ↓
Assign Plan Features
    ↓
CheckFeatureAvailable Middleware
    ↓
Grant/Deny Feature Access
```

### Permission Flow

```
User Action
    ↓
Check User Roles (in tenant)
    ↓
Check Role Permissions
    ↓
Allow/Deny Access
```

---

## 📊 Database Schema

### Tenants Table
```sql
CREATE TABLE tenants (
  id BIGINT PRIMARY KEY,
  name VARCHAR(255),
  slug VARCHAR(255) UNIQUE,
  domain VARCHAR(255) UNIQUE,
  plan VARCHAR(50) DEFAULT 'starter',
  plan_expires_at TIMESTAMP,
  is_active BOOLEAN DEFAULT true,
  settings JSON,
  features JSON,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  deleted_at TIMESTAMP
);
```

### Subscriptions Table
```sql
CREATE TABLE subscriptions (
  id BIGINT PRIMARY KEY,
  tenant_id BIGINT,
  stripe_id VARCHAR(255),
  stripe_status VARCHAR(50),
  plan VARCHAR(50),
  quantity INT DEFAULT 1,
  trial_ends_at TIMESTAMP,
  ends_at TIMESTAMP,
  metadata JSON,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

### Roles Table
```sql
CREATE TABLE roles (
  id BIGINT PRIMARY KEY,
  tenant_id BIGINT,
  name VARCHAR(255),
  slug VARCHAR(255) UNIQUE,
  description TEXT,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

### Permissions Table
```sql
CREATE TABLE permissions (
  id BIGINT PRIMARY KEY,
  name VARCHAR(255),
  slug VARCHAR(255) UNIQUE,
  description TEXT,
  category VARCHAR(100),
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

---

## 💻 Usage Examples

### Create a Tenant

```php
use App\Services\TenantService;

$tenantService = new TenantService();

$tenant = $tenantService->createTenant([
    'name' => 'City Hospital',
    'domain' => 'cityhospital.bloodbank.local',
    'plan' => 'professional',
]);
```

### Create User for Tenant

```php
$user = User::create([
    'tenant_id' => $tenant->id,
    'username' => 'admin@cityhospital.com',
    'email' => 'admin@cityhospital.com',
    'password' => bcrypt('password'),
    'full_name' => 'Hospital Admin',
]);
```

### Assign Role to User

```php
$user->assignRole('tenant_admin', $tenant->id);
```

### Check Permission

```php
// In controller
if (auth()->user()->hasPermission('record_donation')) {
    // Allow donation recording
}

// In blade template
@if(auth()->user()->hasPermission('record_donation'))
    <button>Record Donation</button>
@endif
```

### Check Feature Available

```php
// In controller
if (auth()->user()->tenant->hasFeature('analytics')) {
    // Show analytics dashboard
}

// In routes
Route::get('/analytics', 'AnalyticsController@show')
    ->middleware('check_feature:analytics');
```

### Upgrade Subscription

```php
use App\Services\SubscriptionService;

$subscriptionService = new SubscriptionService();

$subscription = $tenant->subscription;
$subscriptionService->upgrade($subscription, 'enterprise');
```

---

## 🔐 Security Features

### Automatic Tenant Isolation
- Global scope on all tenant-aware models
- Automatic `tenant_id` assignment
- Prevents cross-tenant data leakage

### Role-Based Access Control
- Granular permissions per category
- Tenant-scoped roles
- Per-user role assignment

### Feature Gating
- Plan-based feature access
- Middleware enforcement
- Configurable per tenant

### Subscription Verification
- Check subscription status before access
- Trial period support
- Automatic expiration handling

---

## 🧪 Testing Phase 1

### Manual Testing Checklist

- [ ] Create a tenant
- [ ] Create user for tenant
- [ ] Assign role to user
- [ ] Check role permissions
- [ ] Check feature availability
- [ ] Upgrade subscription
- [ ] Verify tenant isolation (data from one tenant not visible in another)

### Unit Tests to Add

```php
// Test tenant creation
test('can create tenant', function() {
    $tenant = Tenant::factory()->create();
    $this->assertNotNull($tenant->id);
});

// Test user-role assignment
test('can assign role to user', function() {
    $user = User::factory()->create();
    $role = Role::factory()->create();
    $user->assignRole($role);
    $this->assertTrue($user->hasRole($role));
});

// Test permission checking
test('user with permission can access', function() {
    $user = User::factory()->create();
    $role = Role::factory()->create();
    $permission = Permission::factory()->create();
    
    $role->grantPermission($permission);
    $user->assignRole($role);
    
    $this->assertTrue($user->hasPermission($permission));
});
```

---

## 📝 Next Steps (Phase 2)

- [ ] Build tenant onboarding flow
- [ ] Create subscription checkout page (Stripe)
- [ ] Build donor portal interface
- [ ] Add appointment scheduling
- [ ] Implement advanced analytics
- [ ] Create mobile-responsive dashboard

---

## 📞 Support

For questions about Phase 1 implementation:
1. Check SAAS_IMPLEMENTATION_GUIDE.md for code examples
2. Review migrations for database schema
3. Check models for relationships
4. Review middleware for request flow

---

## 🔗 Related Documentation

- [SAAS_FEATURE_ROADMAP.md](../SAAS_FEATURE_ROADMAP.md) - Full roadmap
- [SAAS_IMPLEMENTATION_GUIDE.md](../SAAS_IMPLEMENTATION_GUIDE.md) - Detailed code examples
- [SAAS_PRICING_GUIDE.md](../SAAS_PRICING_GUIDE.md) - Pricing & financial models
- [SAAS_VISUAL_ROADMAP.md](../SAAS_VISUAL_ROADMAP.md) - Growth trajectory

---

**Last Updated:** January 24, 2026

**Version:** Phase 1 (v0.1.0)

**Status:** 🚀 Development in Progress
