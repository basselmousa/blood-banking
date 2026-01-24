<?php

namespace App\Http\Controllers;

use App\Models\DonorPortalSettings;
use App\Models\Tenant;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Show analytics dashboard.
     */
    public function dashboard(Request $request)
    {
        $tenant = auth()->user()->tenant;
        $period = $request->query('period', 'month');

        $metrics = $this->analyticsService->getDashboardMetrics($tenant->id, $period);

        return view('analytics.dashboard', compact('metrics', 'period'));
    }

    /**
     * Get donor metrics.
     */
    public function donors(Request $request)
    {
        $tenant = auth()->user()->tenant;
        $period = $request->query('period', 'month');

        $metrics = $this->analyticsService->getDonorMetrics($tenant->id, $period);

        return response()->json($metrics);
    }

    /**
     * Get donation metrics.
     */
    public function donations(Request $request)
    {
        $tenant = auth()->user()->tenant;
        $period = $request->query('period', 'month');

        $metrics = $this->analyticsService->getDonationMetrics($tenant->id, $period);

        return response()->json($metrics);
    }

    /**
     * Get inventory metrics.
     */
    public function inventory(Request $request)
    {
        $tenant = auth()->user()->tenant;

        $metrics = $this->analyticsService->getInventoryMetrics($tenant->id);

        return response()->json($metrics);
    }

    /**
     * Get appointment metrics.
     */
    public function appointments(Request $request)
    {
        $tenant = auth()->user()->tenant;
        $period = $request->query('period', 'month');

        $metrics = $this->analyticsService->getAppointmentMetrics($tenant->id, $period);

        return response()->json($metrics);
    }

    /**
     * Get revenue metrics.
     */
    public function revenue(Request $request)
    {
        $tenant = auth()->user()->tenant;
        $period = $request->query('period', 'month');

        $metrics = $this->analyticsService->getRevenueMetrics($tenant->id, $period);

        return response()->json($metrics);
    }

    /**
     * Export analytics report.
     */
    public function export(Request $request)
    {
        $tenant = auth()->user()->tenant;
        $period = $request->query('period', 'month');

        $json = $this->analyticsService->exportMetrics($tenant->id, $period);

        return response()->streamDownload(function () use ($json) {
            echo $json;
        }, 'analytics-' . now()->format('Y-m-d') . '.json');
    }

    /**
     * Get real-time metrics.
     */
    public function realtime(Request $request)
    {
        $tenant = auth()->user()->tenant;

        return response()->json([
            'donors' => $this->analyticsService->getDonorMetrics($tenant->id, 'month'),
            'donations' => $this->analyticsService->getDonationMetrics($tenant->id, 'month'),
            'inventory' => $this->analyticsService->getInventoryMetrics($tenant->id),
            'appointments' => $this->analyticsService->getAppointmentMetrics($tenant->id, 'month'),
            'timestamp' => now(),
        ]);
    }
}
