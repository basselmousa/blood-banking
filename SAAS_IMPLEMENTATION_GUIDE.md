# SaaS Implementation Guide - Quick Start

## 🎯 Top 5 Most Important SaaS Features to Build First

### 1. Multi-Tenancy Architecture ⭐⭐⭐⭐⭐
### 2. Subscription Management ⭐⭐⭐⭐⭐
### 3. Enhanced Role-Based Access Control ⭐⭐⭐⭐
### 4. Expanded REST API ⭐⭐⭐⭐
### 5. Analytics Dashboard ⭐⭐⭐⭐

---

## Step 1: Multi-Tenancy Implementation

### Database Structure
```sql
-- Add tenant support to existing tables
ALTER TABLE users ADD COLUMN tenant_id BIGINT UNSIGNED NULLABLE;
ALTER TABLE donors ADD COLUMN tenant_id BIGINT UNSIGNED NULLABLE;
ALTER TABLE donation_records ADD COLUMN tenant_id BIGINT UNSIGNED NULLABLE;
ALTER TABLE donation_deferrals ADD COLUMN tenant_id BIGINT UNSIGNED NULLABLE;

-- Create tenants table
CREATE TABLE tenants (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    domain VARCHAR(255) UNIQUE NULLABLE,
    logo_url VARCHAR(255) NULLABLE,
    branding_color VARCHAR(7) DEFAULT '#667eea',
    subscription_plan VARCHAR(50) DEFAULT 'starter',
    max_donors INT DEFAULT 100,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Create subscription table
CREATE TABLE subscriptions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    plan VARCHAR(50) NOT NULL,
    stripe_subscription_id VARCHAR(255),
    status VARCHAR(50) DEFAULT 'active',
    current_period_start TIMESTAMP,
    current_period_end TIMESTAMP,
    amount DECIMAL(10, 2),
    payment_method VARCHAR(50),
    auto_renew BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY(tenant_id) REFERENCES tenants(id)
);

-- Create usage tracking table
CREATE TABLE usage_tracking (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    metric VARCHAR(100),
    count INT DEFAULT 0,
    month DATE,
    created_at TIMESTAMP,
    FOREIGN KEY(tenant_id) REFERENCES tenants(id)
);
```

### Tenant Model
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'domain',
        'logo_url',
        'branding_color',
        'subscription_plan',
        'max_donors',
        'is_active'
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function donors(): HasMany
    {
        return $this->hasMany(Donor::class);
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class)->latest();
    }

    public function isActive(): bool
    {
        return $this->is_active && 
               $this->subscription?->status === 'active';
    }

    public function canAddDonor(): bool
    {
        return $this->donors()->count() < $this->max_donors;
    }

    public static function byDomain(string $domain): ?self
    {
        return static::where('domain', $domain)
            ->orWhere('slug', $domain)
            ->first();
    }
}
```

### Tenant Middleware
```php
<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Tenant;

class ResolveTenant
{
    public function handle($request, Closure $next)
    {
        // Get tenant from domain, subdomain, or user
        $tenant = $this->resolveTenant($request);

        if (!$tenant) {
            return response()->json([
                'error' => 'Invalid tenant'
            ], 403);
        }

        app()->bind('tenant', $tenant);
        $request->merge(['tenant_id' => $tenant->id]);

        return $next($request);
    }

    protected function resolveTenant($request): ?Tenant
    {
        $host = $request->getHost();

        // Check if subdomain matches tenant
        if (str_contains($host, 'app.')) {
            // Use authenticated user's tenant
            return auth()->user()?->tenant;
        }

        // Check custom domain
        return Tenant::byDomain($host);
    }
}
```

### Model Trait for Auto-Tenancy
```php
<?php

namespace App\Traits;

trait BelongsToTenant
{
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->tenant_id && !app()->runningInConsole()) {
                $model->tenant_id = tenant('id');
            }
        });

        static::addGlobalScope('tenant', function ($query) {
            if (!app()->runningInConsole()) {
                $query->where('tenant_id', tenant('id'));
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
```

### Update Models
```php
// In Donor.php, DonationRecord.php, etc.
use App\Traits\BelongsToTenant;

class Donor extends Model
{
    use BelongsToTenant;
    
    protected $fillable = ['tenant_id', ...];
}
```

---

## Step 2: Subscription Management with Stripe

### Subscription Model
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'tenant_id',
        'plan',
        'stripe_subscription_id',
        'status',
        'current_period_start',
        'current_period_end',
        'amount',
        'payment_method',
        'auto_renew'
    ];

    protected $dates = ['current_period_start', 'current_period_end'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && 
               $this->current_period_end > now();
    }

    public function isExpiring(): bool
    {
        return $this->current_period_end->diffInDays(now()) <= 7;
    }

    public function cancel()
    {
        \Stripe\Subscription::retrieve($this->stripe_subscription_id)
            ->cancel();
        
        $this->update(['status' => 'cancelled']);
    }
}
```

### Subscription Service
```php
<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\Subscription;
use Stripe\StripeClient;

class SubscriptionService
{
    protected $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    public function createSubscription(Tenant $tenant, string $plan, $paymentMethodId): Subscription
    {
        $pricing = $this->getPricing($plan);

        $stripeSubscription = $this->stripe->subscriptions->create([
            'customer' => $tenant->stripe_customer_id,
            'items' => [['price' => $pricing['stripe_price_id']]],
            'payment_behavior' => 'default_incomplete',
            'payment_settings' => ['save_default_payment_method' => 'on_subscription'],
            'expand' => ['latest_invoice.payment_intent'],
        ]);

        return Subscription::create([
            'tenant_id' => $tenant->id,
            'plan' => $plan,
            'stripe_subscription_id' => $stripeSubscription->id,
            'status' => 'active',
            'current_period_start' => now(),
            'current_period_end' => now()->addMonths(1),
            'amount' => $pricing['price'],
        ]);
    }

    public function upgradePlan(Subscription $subscription, string $newPlan): Subscription
    {
        $pricing = $this->getPricing($newPlan);

        $this->stripe->subscriptions->update($subscription->stripe_subscription_id, [
            'items' => [['id' => $subscription->stripe_item_id, 'price' => $pricing['stripe_price_id']]],
            'proration_behavior' => 'create_prorations',
        ]);

        $subscription->update(['plan' => $newPlan, 'amount' => $pricing['price']]);
        
        return $subscription;
    }

    public function handleWebhook($event)
    {
        switch ($event->type) {
            case 'customer.subscription.updated':
                $this->handleSubscriptionUpdated($event->data->object);
                break;
            case 'customer.subscription.deleted':
                $this->handleSubscriptionCancelled($event->data->object);
                break;
            case 'invoice.payment_failed':
                $this->handlePaymentFailed($event->data->object);
                break;
        }
    }

    private function getPricing(string $plan): array
    {
        return [
            'starter' => ['price' => 99, 'stripe_price_id' => 'price_starter'],
            'professional' => ['price' => 299, 'stripe_price_id' => 'price_professional'],
            'enterprise' => ['price' => 999, 'stripe_price_id' => 'price_enterprise'],
        ][$plan];
    }
}
```

### Subscription Controller
```php
<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(protected SubscriptionService $subscriptionService)
    {}

    public function create(Request $request)
    {
        $validated = $request->validate([
            'plan' => 'required|in:starter,professional,enterprise',
            'payment_method' => 'required|string'
        ]);

        $tenant = auth()->user()->tenant;

        $subscription = $this->subscriptionService->createSubscription(
            $tenant,
            $validated['plan'],
            $validated['payment_method']
        );

        return response()->json($subscription);
    }

    public function upgrade(Request $request)
    {
        $validated = $request->validate([
            'plan' => 'required|in:starter,professional,enterprise'
        ]);

        $tenant = auth()->user()->tenant;
        $subscription = $this->subscriptionService->upgradePlan(
            $tenant->subscription,
            $validated['plan']
        );

        return response()->json([
            'message' => 'Plan upgraded successfully',
            'subscription' => $subscription
        ]);
    }

    public function webhook(Request $request)
    {
        $event = \Stripe\Event::constructFrom(
            json_decode($request->getContent(), true)
        );

        $this->subscriptionService->handleWebhook($event);

        return response()->json(['received' => true]);
    }
}
```

---

## Step 3: Enhanced Role-Based Access Control

### Add Roles & Permissions Tables
```sql
CREATE TABLE roles (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP,
    FOREIGN KEY(tenant_id) REFERENCES tenants(id)
);

CREATE TABLE permissions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP
);

CREATE TABLE role_permission (
    role_id BIGINT UNSIGNED,
    permission_id BIGINT UNSIGNED,
    PRIMARY KEY(role_id, permission_id),
    FOREIGN KEY(role_id) REFERENCES roles(id),
    FOREIGN KEY(permission_id) REFERENCES permissions(id)
);

CREATE TABLE user_role (
    user_id BIGINT UNSIGNED,
    role_id BIGINT UNSIGNED,
    PRIMARY KEY(user_id, role_id),
    FOREIGN KEY(user_id) REFERENCES users(id),
    FOREIGN KEY(role_id) REFERENCES roles(id)
);
```

### Role Model
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['tenant_id', 'name', 'description'];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
```

### Authorization Gate
```php
// In AuthServiceProvider
Gate::define('manage-donors', function (User $user) {
    return $user->hasPermission('donors.manage');
});

Gate::define('view-reports', function (User $user) {
    return $user->hasPermission('reports.view');
});

Gate::define('manage-users', function (User $user) {
    return $user->isAdmin();
});
```

---

## Step 4: Expanded REST API

### API Routes with Versioning
```php
// routes/api.php
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::apiResource('donors', DonorController::class);
    Route::apiResource('donations', DonationRecordController::class);
    Route::apiResource('deferrals', DonationDeferralController::class);
    
    Route::get('/analytics/donors', [AnalyticsController::class, 'donors']);
    Route::get('/analytics/donations', [AnalyticsController::class, 'donations']);
    Route::get('/inventory/status', [InventoryController::class, 'status']);
});
```

### API Resource Controller
```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\DonorResource;
use App\Models\Donor;
use Illuminate\Http\Request;

class DonorController
{
    public function index(Request $request)
    {
        $donors = Donor::filter($request->all())
            ->paginate($request->per_page ?? 15);

        return DonorResource::collection($donors);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'email' => 'required|email|unique:donors',
            'blood_group' => 'required|in:O-,O+,A-,A+,B-,B+,AB-,AB+',
            // ... more validations
        ]);

        $donor = Donor::create($validated);

        return new DonorResource($donor);
    }

    public function show(Donor $donor)
    {
        return new DonorResource($donor);
    }

    public function update(Request $request, Donor $donor)
    {
        $donor->update($request->validated());
        return new DonorResource($donor);
    }

    public function destroy(Donor $donor)
    {
        $donor->delete();
        return response()->noContent();
    }
}
```

### API Rate Limiting
```php
// Configure in config/api.php
'rate_limit' => [
    'starter' => 100,      // 100 requests/hour
    'professional' => 1000,
    'enterprise' => 10000,
]

// Middleware
class RateLimitByPlan
{
    public function handle($request, Closure $next)
    {
        $tenant = tenant();
        $limit = config('api.rate_limit.' . $tenant->subscription->plan);

        RateLimiter::for('api', function (Request $request) use ($limit) {
            return Limit::perHour($limit)
                ->by($request->user()?->id ?: $request->ip());
        });

        return $next($request);
    }
}
```

---

## Step 5: Analytics Dashboard

### Analytics Service
```php
<?php

namespace App\Services;

use App\Models\Donor;
use App\Models\DonationRecord;
use App\Models\DonationDeferral;
use Illuminate\Support\Facades\Cache;

class AnalyticsService
{
    public function getDashboardMetrics(int $tenantId)
    {
        return Cache::remember(
            "analytics.dashboard.{$tenantId}",
            3600,
            function () {
                return [
                    'total_donors' => $this->getTotalDonors(),
                    'eligible_count' => $this->getEligibleDonors(),
                    'deferred_count' => $this->getDeferredDonors(),
                    'donations_this_month' => $this->getDonationsThisMonth(),
                    'rejection_rate' => $this->getRejectionRate(),
                    'blood_group_distribution' => $this->getBloodGroupDistribution(),
                    'donation_trends' => $this->getDonationTrends(),
                ];
            }
        );
    }

    private function getTotalDonors()
    {
        return Donor::count();
    }

    private function getEligibleDonors()
    {
        $count = 0;
        Donor::chunk(100, function ($donors) use (&$count) {
            foreach ($donors as $donor) {
                if ((new \App\Services\DonationEligibilityService())->isEligible($donor)['eligible']) {
                    $count++;
                }
            }
        });
        return $count;
    }

    private function getDeferredDonors()
    {
        return Donor::where('is_deferred', true)->count();
    }

    private function getDonationsThisMonth()
    {
        return DonationRecord::whereMonth('donation_date', now()->month)
            ->where('status', 'completed')
            ->count();
    }

    private function getRejectionRate()
    {
        $total = DonationRecord::count();
        $rejected = DonationRecord::where('status', 'rejected')->count();
        return $total > 0 ? round(($rejected / $total) * 100, 2) : 0;
    }

    private function getBloodGroupDistribution()
    {
        return Donor::selectRaw('blood_group, COUNT(*) as count')
            ->groupBy('blood_group')
            ->get();
    }

    private function getDonationTrends()
    {
        return DonationRecord::selectRaw('DATE(donation_date) as date, COUNT(*) as count')
            ->where('status', 'completed')
            ->where('donation_date', '>=', now()->subDays(30))
            ->groupBy('date')
            ->get();
    }
}
```

### Analytics Controller
```php
<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;

class AnalyticsController
{
    public function __construct(protected AnalyticsService $analyticsService)
    {}

    public function dashboard()
    {
        $metrics = $this->analyticsService->getDashboardMetrics(tenant('id'));

        return view('analytics.dashboard', compact('metrics'));
    }

    public function exportReport()
    {
        // Generate PDF/Excel report
        return \PDF::generate($metrics);
    }
}
```

---

## Routes Configuration

### Add to routes/web.php
```php
Route::prefix('saas')->middleware(['auth', 'verified'])->group(function () {
    // Subscription routes
    Route::get('/subscription', [SubscriptionController::class, 'show'])->name('subscription.show');
    Route::post('/subscription', [SubscriptionController::class, 'create'])->name('subscription.create');
    Route::post('/subscription/upgrade', [SubscriptionController::class, 'upgrade'])->name('subscription.upgrade');
    
    // Tenant settings
    Route::get('/settings', [TenantSettingsController::class, 'edit'])->name('settings.edit');
    Route::post('/settings', [TenantSettingsController::class, 'update'])->name('settings.update');
    
    // Users & roles
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
    
    // Analytics
    Route::get('/analytics', [AnalyticsController::class, 'dashboard'])->name('analytics.dashboard');
});

Route::webhook('stripe', [SubscriptionController::class, 'webhook']);
```

---

## Environment Configuration

### Add to .env
```env
STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...

TENANCY_ENABLED=true
TENANCY_MODE=domain  # or schema

# Feature flags
FEATURE_MULTI_TENANT=true
FEATURE_SUBSCRIPTIONS=true
FEATURE_ANALYTICS=true
FEATURE_API_V2=true
```

---

## Quick Checklist

```
✅ Database migrations created
✅ Tenant model implemented
✅ Multi-tenancy middleware added
✅ Subscription service created
✅ Role-based access control implemented
✅ API routes expanded
✅ Analytics service built
✅ Configuration updated
✅ Tests written
✅ Documentation updated
```

---

**This foundation enables you to scale from single-instance to multi-tenant SaaS!**

Would you like me to implement any of these specific components?
