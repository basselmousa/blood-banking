<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'domain',
        'plan',
        'plan_expires_at',
        'is_active',
        'settings',
        'features',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'plan_expires_at' => 'datetime',
        'is_active' => 'boolean',
        'settings' => 'json',
        'features' => 'json',
    ];

    /**
     * Get the users for the tenant.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the donors for the tenant.
     */
    public function donors()
    {
        return $this->hasMany(Donor::class);
    }

    /**
     * Get the patients for the tenant.
     */
    public function patients()
    {
        return $this->hasMany(Patient::class);
    }

    /**
     * Get the subscription for the tenant.
     */
    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    /**
     * Get the roles for the tenant.
     */
    public function roles()
    {
        return $this->hasMany(Role::class);
    }

    /**
     * Check if tenant has a feature.
     */
    public function hasFeature($feature)
    {
        $features = $this->features ?? [];
        return in_array($feature, $features);
    }

    /**
     * Get the plan configuration.
     */
    public function getPlanConfig()
    {
        $plans = [
            'starter' => [
                'name' => 'Starter',
                'price' => 99,
                'donors_limit' => 100,
                'users_limit' => 3,
                'features' => ['basic_eligibility', 'email_support'],
            ],
            'professional' => [
                'name' => 'Professional',
                'price' => 299,
                'donors_limit' => 1000,
                'users_limit' => 10,
                'features' => ['basic_eligibility', 'analytics', 'donor_portal', 'appointments', 'phone_support'],
            ],
            'enterprise' => [
                'name' => 'Enterprise',
                'price' => 999,
                'donors_limit' => null, // unlimited
                'users_limit' => null, // unlimited
                'features' => ['basic_eligibility', 'analytics', 'donor_portal', 'appointments', 'mobile_app', 'custom_integrations', 'api_access', '24_7_support'],
            ],
        ];

        return $plans[$this->plan] ?? $plans['starter'];
    }

    /**
     * Check if subscription is active.
     */
    public function isActive()
    {
        return $this->is_active && ($this->subscription()->active()->exists() || $this->plan === 'enterprise');
    }
}
