<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Show subscription management page.
     */
    public function show(Tenant $tenant)
    {
        $subscription = $tenant->subscription;
        $planConfig = $tenant->getPlanConfig();
        $availablePlans = $this->getAvailablePlans();

        return view('billing.subscription', compact('subscription', 'planConfig', 'availablePlans'));
    }

    /**
     * Upgrade subscription.
     */
    public function upgrade(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'plan' => 'required|in:starter,professional,enterprise',
        ]);

        $subscription = $tenant->subscription;

        if ($subscription) {
            $this->subscriptionService->upgrade($subscription, $validated['plan']);
        } else {
            $this->subscriptionService->createSubscription($tenant, $validated['plan']);
        }

        return redirect()->route('billing.subscription', $tenant)
            ->with('success', 'Subscription upgraded successfully!');
    }

    /**
     * Downgrade subscription.
     */
    public function downgrade(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'plan' => 'required|in:starter,professional',
        ]);

        $subscription = $tenant->subscription;

        if (!$subscription) {
            return redirect()->back()->with('error', 'No active subscription found.');
        }

        $this->subscriptionService->downgrade($subscription, $validated['plan']);

        return redirect()->route('billing.subscription', $tenant)
            ->with('success', 'Subscription downgraded. Changes will take effect at the end of your billing cycle.');
    }

    /**
     * Cancel subscription.
     */
    public function cancel(Tenant $tenant)
    {
        $subscription = $tenant->subscription;

        if (!$subscription) {
            return redirect()->back()->with('error', 'No active subscription found.');
        }

        $this->subscriptionService->cancel($subscription);

        return redirect()->route('billing.subscription', $tenant)
            ->with('success', 'Subscription cancelled. You will lose access at the end of your billing cycle.');
    }

    /**
     * Resume subscription.
     */
    public function resume(Tenant $tenant)
    {
        $subscription = $tenant->subscription;

        if (!$subscription) {
            return redirect()->back()->with('error', 'No cancelled subscription found.');
        }

        $this->subscriptionService->resume($subscription);

        return redirect()->route('billing.subscription', $tenant)
            ->with('success', 'Subscription resumed successfully!');
    }

    /**
     * Get available plans.
     */
    private function getAvailablePlans()
    {
        return [
            'starter' => [
                'name' => 'Starter',
                'price' => '$99/month',
                'features' => ['Basic Eligibility', 'Email Support'],
            ],
            'professional' => [
                'name' => 'Professional',
                'price' => '$299/month',
                'features' => ['Basic Eligibility', 'Analytics', 'Donor Portal', 'Appointments', 'Phone Support'],
            ],
            'enterprise' => [
                'name' => 'Enterprise',
                'price' => 'Custom',
                'features' => ['Everything in Professional', 'Mobile App', 'Custom Integrations', 'API Access', '24/7 Support'],
            ],
        ];
    }
}
