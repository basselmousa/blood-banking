<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Str;

class TenantService
{
    /**
     * Create a new tenant.
     */
    public function createTenant($data)
    {
        $tenant = Tenant::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'domain' => $data['domain'] ?? null,
            'plan' => $data['plan'] ?? 'starter',
            'is_active' => true,
            'features' => $this->getFeaturesForPlan($data['plan'] ?? 'starter'),
        ]);

        return $tenant;
    }

    /**
     * Get features for a plan.
     */
    public function getFeaturesForPlan($plan)
    {
        $features = [
            'starter' => [
                'basic_eligibility',
                'email_support',
            ],
            'professional' => [
                'basic_eligibility',
                'analytics',
                'donor_portal',
                'appointments',
                'phone_support',
            ],
            'enterprise' => [
                'basic_eligibility',
                'analytics',
                'donor_portal',
                'appointments',
                'mobile_app',
                'custom_integrations',
                'api_access',
                '24_7_support',
            ],
        ];

        return $features[$plan] ?? $features['starter'];
    }

    /**
     * Update tenant plan.
     */
    public function updatePlan(Tenant $tenant, $newPlan)
    {
        $tenant->update([
            'plan' => $newPlan,
            'features' => $this->getFeaturesForPlan($newPlan),
        ]);

        return $tenant;
    }

    /**
     * Check if tenant can add more users.
     */
    public function canAddUser(Tenant $tenant)
    {
        $config = $tenant->getPlanConfig();
        $usersLimit = $config['users_limit'];

        if (is_null($usersLimit)) {
            return true; // Unlimited
        }

        $currentUsers = $tenant->users()->count();
        return $currentUsers < $usersLimit;
    }

    /**
     * Check if tenant can add more donors.
     */
    public function canAddDonor(Tenant $tenant)
    {
        $config = $tenant->getPlanConfig();
        $donorsLimit = $config['donors_limit'];

        if (is_null($donorsLimit)) {
            return true; // Unlimited
        }

        $currentDonors = $tenant->donors()->count();
        return $currentDonors < $donorsLimit;
    }

    /**
     * Get tenant usage statistics.
     */
    public function getUsageStatistics(Tenant $tenant)
    {
        $config = $tenant->getPlanConfig();

        return [
            'users' => [
                'current' => $tenant->users()->count(),
                'limit' => $config['users_limit'],
            ],
            'donors' => [
                'current' => $tenant->donors()->count(),
                'limit' => $config['donors_limit'],
            ],
        ];
    }

    /**
     * Check if tenant is within usage limits.
     */
    public function isWithinLimits(Tenant $tenant)
    {
        return $this->canAddUser($tenant) && $this->canAddDonor($tenant);
    }
}
