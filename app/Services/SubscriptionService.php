<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\Tenant;

class SubscriptionService
{
    /**
     * Create a subscription for a tenant.
     */
    public function createSubscription(Tenant $tenant, $plan, $stripeId = null)
    {
        $subscription = Subscription::create([
            'tenant_id' => $tenant->id,
            'plan' => $plan,
            'stripe_id' => $stripeId,
            'stripe_status' => 'active',
        ]);

        // Update tenant plan
        $tenant->update(['plan' => $plan]);

        return $subscription;
    }

    /**
     * Upgrade a subscription to a new plan.
     */
    public function upgrade(Subscription $subscription, $newPlan)
    {
        $subscription->upgrade($newPlan);
        return $subscription;
    }

    /**
     * Downgrade a subscription to a new plan.
     */
    public function downgrade(Subscription $subscription, $newPlan)
    {
        $subscription->update(['plan' => $newPlan]);
        $subscription->tenant->update(['plan' => $newPlan]);
        return $subscription;
    }

    /**
     * Cancel a subscription.
     */
    public function cancel(Subscription $subscription)
    {
        $subscription->cancel();
        return $subscription;
    }

    /**
     * Resume a cancelled subscription.
     */
    public function resume(Subscription $subscription)
    {
        $subscription->update(['ends_at' => null]);
        return $subscription;
    }

    /**
     * Check if subscription is active.
     */
    public function isActive(Subscription $subscription)
    {
        return !$subscription->cancelled() && !$subscription->expired();
    }

    /**
     * Get subscription details.
     */
    public function getDetails(Subscription $subscription)
    {
        return [
            'plan' => $subscription->plan,
            'status' => $subscription->stripe_status,
            'on_trial' => $subscription->onTrial(),
            'cancelled' => $subscription->cancelled(),
            'trial_ends_at' => $subscription->trial_ends_at,
            'ends_at' => $subscription->ends_at,
        ];
    }

    /**
     * Get the cost of a plan change.
     */
    public function calculateProration(Subscription $subscription, $newPlan)
    {
        // This is a simplified version
        // In production, you'd integrate with Stripe's proration calculation
        return 0;
    }
}
