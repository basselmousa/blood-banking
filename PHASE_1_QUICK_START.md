# Quick Start: Phase 1 SaaS Implementation

**Branch:** `gemini/features/SaaS/phase1`  
**Status:** ✅ Ready to Use  
**Created:** January 24, 2026

---

## 🚀 One-Time Setup (5 minutes)

### 1. Run Migrations
```bash
php artisan migrate
```

### 2. Seed Initial Data
```bash
php artisan db:seed --class=RolePermissionSeeder
```

### 3. Register Routes
Edit `routes/web.php`:
```php
// Add after other route files
require base_path('routes/saas-phase1.php');
```

### 4. Register Middleware
Edit `app/Http/Kernel.php` in `$routeMiddleware`:
```php
'resolve_tenant' => \App\Http\Middleware\ResolveTenant::class,
'check_feature' => \App\Http\Middleware\CheckFeatureAvailable::class,
```

### 5. Update Models (Optional)
Add trait to `Donor` and `Patient` models:
```php
use App\Models\Traits\BelongsToTenant;

class Donor extends Model {
    use BelongsToTenant;
}
```

---

## 📚 Available Endpoints

### Tenant Management
```
GET    /admin/tenants                    - List all tenants
GET    /admin/tenants/create             - Show create form
POST   /admin/tenants                    - Create tenant
GET    /admin/tenants/{tenant}           - Show tenant details
GET    /admin/tenants/{tenant}/edit      - Show edit form
PUT    /admin/tenants/{tenant}           - Update tenant
POST   /admin/tenants/{tenant}/activate   - Activate tenant
POST   /admin/tenants/{tenant}/deactivate - Deactivate tenant
DELETE /admin/tenants/{tenant}           - Delete tenant
```

### Subscription Management
```
GET  /billing/subscription               - View subscription
POST /billing/subscription/upgrade       - Upgrade plan
POST /billing/subscription/downgrade     - Downgrade plan
POST /billing/subscription/cancel        - Cancel subscription
POST /billing/subscription/resume        - Resume subscription
```

---

## 💻 Common Code Patterns

### Create a Tenant
```php
$tenantService = app(\App\Services\TenantService::class);

$tenant = $tenantService->createTenant([
    'name' => 'Hospital Name',
    'domain' => 'hospital.local',
    'plan' => 'professional',
]);
```

### Create User for Tenant
```php
$user = \App\Models\User::create([
    'tenant_id' => $tenant->id,
    'email' => 'admin@hospital.com',
    'password' => bcrypt('password'),
    'full_name' => 'Admin Name',
]);
```

### Assign Role to User
```php
$user->assignRole('tenant_admin', $tenant->id);
```

### Check if User has Permission
```php
if (auth()->user()->hasPermission('record_donation')) {
    // User can record donations
}
```

### Check if Feature is Available
```php
if (auth()->user()->tenant->hasFeature('analytics')) {
    // Feature available for this tenant
}
```

### Limit Feature to Specific Plan
```php
Route::get('/analytics', 'AnalyticsController@show')
    ->middleware('check_feature:analytics');
```

### Get Usage Statistics
```php
$tenantService = app(\App\Services\TenantService::class);
$stats = $tenantService->getUsageStatistics($tenant);

// Returns: ['users' => ['current' => 5, 'limit' => 10], 'donors' => [...]]
```

---

## 🔐 Permission System

### Available Permissions

**Admin Permissions:**
- `manage_tenants` - Create/delete tenants
- `manage_users` - Manage users
- `manage_roles` - Manage roles
- `manage_permissions` - Manage permissions

**Donation Permissions:**
- `create_donation` - Record donations
- `view_donation` - View donation records
- `edit_donation` - Modify donations
- `delete_donation` - Delete donations
- `record_donation` - Record new donation

**Inventory Permissions:**
- `manage_inventory` - Full inventory control
- `view_inventory` - View inventory only

**Patient Permissions:**
- `create_patient` - Create patients
- `view_patient` - View patients
- `edit_patient` - Modify patients

**Donor Permissions:**
- `create_donor` - Add donors
- `view_donor` - View donors
- `edit_donor` - Modify donors
- `delete_donor` - Remove donors

**Billing Permissions:**
- `manage_billing` - Manage billing
- `view_billing` - View billing info

---

## 👥 Default Roles

| Role | Permissions | Use Case |
|------|-------------|----------|
| **Super Admin** | All | System administrators |
| **Tenant Admin** | All except tenants | Organization administrators |
| **Manager** | Donations, Inventory, Donors, Patients, Billing | Department managers |
| **Staff** | Create/Record Donations, View data | Regular staff |
| **Viewer** | View-only access | Auditors, reports |

---

## 📊 Plan Limits

| Feature | Starter | Professional | Enterprise |
|---------|---------|--------------|------------|
| Donors | 100 | 1,000 | Unlimited |
| Users | 3 | 10 | Unlimited |
| Analytics | ❌ | ✅ | ✅ |
| Donor Portal | ❌ | ✅ | ✅ |
| Appointments | ❌ | ✅ | ✅ |
| Mobile App | ❌ | ❌ | ✅ |
| Custom Integration | ❌ | ❌ | ✅ |
| API Access | ❌ | ❌ | ✅ |
| 24/7 Support | ❌ | ❌ | ✅ |

---

## 🔄 Database Schema Quick Reference

### Key Tables
- `tenants` - Organizations
- `subscriptions` - Subscription records
- `roles` - RBAC roles
- `permissions` - Permission catalog
- `users` - User accounts (with tenant_id)
- `donors` - Donors (with tenant_id)
- `patients` - Patients (with tenant_id)

### Pivot Tables
- `role_permissions` - Role to Permission mapping
- `user_roles` - User to Role mapping (tenant-scoped)

---

## 🧪 Testing Phase 1

### Test Tenant Isolation
```php
// Login as user in Tenant A
$userA = User::where('tenant_id', 1)->first();
auth()->login($userA);

// Try to access Tenant B's donors
$donorsB = Donor::where('tenant_id', 2)->get();
// Result: Empty (global scope filters by tenant_id)
```

### Test Permissions
```php
$user->assignRole('staff');
auth()->login($user);

// Should return true
auth()->user()->hasPermission('record_donation');

// Should return false
auth()->user()->hasPermission('manage_users');
```

### Test Feature Gating
```php
$tenant->update(['features' => ['analytics']]);

// Should be available
$tenant->hasFeature('analytics'); // true

// Should not be available
$tenant->hasFeature('mobile_app'); // false
```

---

## 🐛 Troubleshooting

### "Tenant not resolved"
Make sure `ResolveTenant` middleware is registered and applied to routes.

### "Permission denied"
Check if user has role assigned via `$user->assignRole('role_slug', $tenant->id)`.

### "Feature not available"
Verify `CheckFeatureAvailable` middleware is applied and tenant has feature in features JSON.

### "Cross-tenant data leak"
Ensure models use `BelongsToTenant` trait to enable automatic global scoping.

---

## 📁 File Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── TenantController.php      ← Create/manage tenants
│   │   └── SubscriptionController.php ← Manage subscriptions
│   └── Middleware/
│       ├── ResolveTenant.php          ← Load tenant context
│       └── CheckFeatureAvailable.php  ← Verify feature access
├── Models/
│   ├── Tenant.php                     ← Organization model
│   ├── Subscription.php               ← Subscription model
│   ├── Role.php                       ← Role model
│   ├── Permission.php                 ← Permission model
│   └── Traits/
│       ├── BelongsToTenant.php        ← Auto-isolation
│       └── HasRolesAndPermissions.php ← Authorization
└── Services/
    ├── TenantService.php              ← Tenant logic
    └── SubscriptionService.php        ← Subscription logic

database/
├── migrations/
│   ├── 2026_01_24_000001_create_tenants_table.php
│   ├── 2026_01_24_000002_create_subscriptions_table.php
│   ├── 2026_01_24_000003_create_roles_and_permissions_table.php
│   └── 2026_01_24_000004_add_tenant_id_to_existing_tables.php
└── seeders/
    └── RolePermissionSeeder.php       ← Initial roles/perms

routes/
└── saas-phase1.php                    ← Phase 1 endpoints

docs/
├── PHASE_1_README.md                  ← Full guide
├── PHASE_1_IMPLEMENTATION_SUMMARY.md  ← Implementation details
└── PHASE_1_QUICK_START.md             ← This file!
```

---

## 📞 Need Help?

1. **Detailed Implementation** → See [PHASE_1_README.md](PHASE_1_README.md)
2. **Code Examples** → Check [SAAS_IMPLEMENTATION_GUIDE.md](SAAS_IMPLEMENTATION_GUIDE.md)
3. **Architecture Details** → Review [PHASE_1_IMPLEMENTATION_SUMMARY.md](PHASE_1_IMPLEMENTATION_SUMMARY.md)
4. **Full Roadmap** → Consult [SAAS_FEATURE_ROADMAP.md](SAAS_FEATURE_ROADMAP.md)

---

## 📈 Next Phase

**Phase 2 (Coming Soon):**
- Tenant onboarding flow
- Stripe payment integration
- Donor portal interface
- Appointment scheduling
- Advanced analytics dashboard

---

**Version:** Phase 1 v0.1.0  
**Last Updated:** January 24, 2026  
**Branch:** `gemini/features/SaaS/phase1`
