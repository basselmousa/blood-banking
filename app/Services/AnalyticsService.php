<?php

namespace App\Services;

use App\Models\Analytics;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Record a metric.
     */
    public function recordMetric($tenantId, $metricType, $metricName, $count = 0, $value = 0, $metadata = null)
    {
        Analytics::create([
            'tenant_id' => $tenantId,
            'metric_type' => $metricType,
            'metric_name' => $metricName,
            'metric_date' => now(),
            'count' => $count,
            'value' => $value,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Get donor metrics.
     */
    public function getDonorMetrics($tenantId, $period = 'month')
    {
        $startDate = $this->getPeriodStartDate($period);

        return [
            'total_donors' => DB::table('donors')
                ->where('tenant_id', $tenantId)
                ->count(),
            'new_donors' => DB::table('donors')
                ->where('tenant_id', $tenantId)
                ->whereBetween('created_at', [$startDate, now()])
                ->count(),
            'active_donors' => DB::table('donors')
                ->where('tenant_id', $tenantId)
                ->whereNotNull('last_donation_date')
                ->whereBetween('last_donation_date', [$startDate, now()])
                ->count(),
            'inactive_donors' => DB::table('donors')
                ->where('tenant_id', $tenantId)
                ->where(function ($q) use ($startDate) {
                    $q->whereNull('last_donation_date')
                        ->orWhere('last_donation_date', '<', $startDate);
                })
                ->count(),
        ];
    }

    /**
     * Get donation metrics.
     */
    public function getDonationMetrics($tenantId, $period = 'month')
    {
        $startDate = $this->getPeriodStartDate($period);

        return [
            'total_donations' => DB::table('donation_records')
                ->where('tenant_id', $tenantId)
                ->whereBetween('donation_date', [$startDate, now()])
                ->count(),
            'successful_donations' => DB::table('donation_records')
                ->where('tenant_id', $tenantId)
                ->where('status', 'completed')
                ->whereBetween('donation_date', [$startDate, now()])
                ->count(),
            'deferred_donors' => DB::table('donation_deferrals')
                ->where('tenant_id', $tenantId)
                ->whereBetween('created_at', [$startDate, now()])
                ->count(),
            'ineligible_donors' => DB::table('donation_records')
                ->where('tenant_id', $tenantId)
                ->where('status', 'ineligible')
                ->whereBetween('donation_date', [$startDate, now()])
                ->count(),
        ];
    }

    /**
     * Get inventory metrics.
     */
    public function getInventoryMetrics($tenantId)
    {
        return [
            'total_units' => DB::table('inventory')
                ->where('tenant_id', $tenantId)
                ->sum('quantity'),
            'units_below_critical' => DB::table('inventory')
                ->where('tenant_id', $tenantId)
                ->whereRaw('quantity <= critical_level')
                ->count(),
            'expiring_soon' => DB::table('inventory')
                ->where('tenant_id', $tenantId)
                ->whereBetween('expiration_date', [now(), now()->addDays(7)])
                ->count(),
            'expired_units' => DB::table('inventory')
                ->where('tenant_id', $tenantId)
                ->where('expiration_date', '<', now())
                ->count(),
            'blood_type_breakdown' => DB::table('inventory')
                ->where('tenant_id', $tenantId)
                ->select('blood_type', DB::raw('SUM(quantity) as total'))
                ->groupBy('blood_type')
                ->get()
                ->keyBy('blood_type'),
        ];
    }

    /**
     * Get appointment metrics.
     */
    public function getAppointmentMetrics($tenantId, $period = 'month')
    {
        $startDate = $this->getPeriodStartDate($period);

        return [
            'total_appointments' => DB::table('appointments')
                ->where('tenant_id', $tenantId)
                ->whereBetween('scheduled_at', [$startDate, now()])
                ->count(),
            'completed_appointments' => DB::table('appointments')
                ->where('tenant_id', $tenantId)
                ->where('status', 'completed')
                ->whereBetween('completed_at', [$startDate, now()])
                ->count(),
            'scheduled_appointments' => DB::table('appointments')
                ->where('tenant_id', $tenantId)
                ->where('status', 'scheduled')
                ->whereDate('scheduled_at', '>=', now())
                ->count(),
            'no_shows' => DB::table('appointments')
                ->where('tenant_id', $tenantId)
                ->where('status', 'no_show')
                ->whereBetween('scheduled_at', [$startDate, now()])
                ->count(),
            'cancellations' => DB::table('appointments')
                ->where('tenant_id', $tenantId)
                ->where('status', 'cancelled')
                ->whereBetween('scheduled_at', [$startDate, now()])
                ->count(),
        ];
    }

    /**
     * Get revenue metrics.
     */
    public function getRevenueMetrics($tenantId, $period = 'month')
    {
        $startDate = $this->getPeriodStartDate($period);

        return [
            'mrr' => DB::table('subscriptions')
                ->where('tenant_id', $tenantId)
                ->where('stripe_status', 'active')
                ->sum(DB::raw('CASE WHEN plan = "starter" THEN 99 WHEN plan = "professional" THEN 299 WHEN plan = "enterprise" THEN 999 END')),
            'subscription_count' => DB::table('subscriptions')
                ->where('tenant_id', $tenantId)
                ->where('stripe_status', 'active')
                ->count(),
            'churn_rate' => $this->calculateChurnRate($tenantId, $startDate),
            'new_subscriptions' => DB::table('subscriptions')
                ->where('tenant_id', $tenantId)
                ->whereBetween('created_at', [$startDate, now()])
                ->count(),
        ];
    }

    /**
     * Get dashboard metrics.
     */
    public function getDashboardMetrics($tenantId, $period = 'month')
    {
        return [
            'donors' => $this->getDonorMetrics($tenantId, $period),
            'donations' => $this->getDonationMetrics($tenantId, $period),
            'inventory' => $this->getInventoryMetrics($tenantId),
            'appointments' => $this->getAppointmentMetrics($tenantId, $period),
            'revenue' => $this->getRevenueMetrics($tenantId, $period),
        ];
    }

    /**
     * Get period start date.
     */
    private function getPeriodStartDate($period)
    {
        return match($period) {
            'week' => now()->subDays(7),
            'month' => now()->subDays(30),
            'quarter' => now()->subDays(90),
            'year' => now()->subDays(365),
            default => now()->subDays(30),
        };
    }

    /**
     * Calculate churn rate.
     */
    private function calculateChurnRate($tenantId, $startDate)
    {
        $cancelled = DB::table('subscriptions')
            ->where('tenant_id', $tenantId)
            ->whereNotNull('ends_at')
            ->whereBetween('ends_at', [$startDate, now()])
            ->count();

        $total = DB::table('subscriptions')
            ->where('tenant_id', $tenantId)
            ->count();

        return $total > 0 ? round(($cancelled / $total) * 100, 2) : 0;
    }

    /**
     * Export metrics to CSV.
     */
    public function exportMetrics($tenantId, $period = 'month')
    {
        $metrics = $this->getDashboardMetrics($tenantId, $period);
        return json_encode($metrics, JSON_PRETTY_PRINT);
    }
}
