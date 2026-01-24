<?php

namespace App\Http\Controllers;

use App\Models\Donor;
use App\Models\DonationRecord;
use App\Models\DonationDeferral;
use App\Services\DonationEligibilityService;
use App\Services\DonationRiskAssessmentService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DonationEligibilityController extends Controller
{
    protected $eligibilityService;
    protected $riskAssessmentService;

    public function __construct(
        DonationEligibilityService $eligibilityService,
        DonationRiskAssessmentService $riskAssessmentService
    ) {
        $this->eligibilityService = $eligibilityService;
        $this->riskAssessmentService = $riskAssessmentService;
        $this->middleware(['auth:admin']);
    }

    /**
     * Check eligibility for a specific donor
     */
    public function checkEligibility(Donor $donor)
    {
        $eligibility = $this->eligibilityService->isEligible($donor);
        $riskDetails = $this->riskAssessmentService->getRiskDetails($donor);
        $donationHistory = $donor->donationRecords()
            ->orderBy('donation_date', 'desc')
            ->limit(5)
            ->get();

        return view('admin.dashboard.check_eligibility', compact('donor', 'eligibility', 'riskDetails', 'donationHistory'));
    }

    /**
     * List all eligible donors for a blood group
     */
    public function listEligible(Request $request)
    {
        $request->validate([
            'blood_group' => 'required|string',
            'city' => 'nullable|string'
        ]);

        $donors = Donor::byBloodGroup($request->blood_group)
            ->byCity($request->city ?? 'all')
            ->active()
            ->get();

        $eligibleDonors = [];
        foreach ($donors as $donor) {
            $eligibility = $this->eligibilityService->isEligible($donor);
            if ($eligibility['eligible']) {
                $eligibleDonors[] = [
                    'donor' => $donor,
                    'next_eligible' => $eligibility['next_eligible_date'],
                    'risk_level' => $eligibility['risk_level']
                ];
            }
        }

        return view('admin.dashboard.eligible_donors_list', compact('eligibleDonors', 'request'));
    }

    /**
     * List deferred donors
     */
    public function listDeferred()
    {
        $deferrals = DonationDeferral::where('is_active', true)
            ->with('donor')
            ->orderBy('eligible_after', 'asc')
            ->paginate(20);

        return view('admin.dashboard.deferred_donors_list', compact('deferrals'));
    }

    /**
     * Create a deferral for a donor
     */
    public function defer(Request $request, Donor $donor)
    {
        $request->validate([
            'reason' => 'required|string',
            'description' => 'required|string',
            'deferral_type' => 'required|in:temporary,permanent,conditional',
            'eligible_after' => 'nullable|date|after:today'
        ]);

        $eligibleAfter = null;
        if ($request->eligible_after) {
            $eligibleAfter = Carbon::parse($request->eligible_after);
        } elseif ($request->deferral_type === 'temporary') {
            $eligibleAfter = now()->addMonths(1);
        }

        $this->eligibilityService->deferDonor(
            $donor,
            $request->reason,
            $request->description,
            $eligibleAfter,
            $request->deferral_type
        );

        return redirect()->route('admin.donors')
            ->with('success', "Donor {$donor->full_name} has been deferred");
    }

    /**
     * Clear deferral for a donor
     */
    public function clearDeferral(Donor $donor)
    {
        $this->eligibilityService->clearDeferral($donor);

        return redirect()->route('admin.donors')
            ->with('success', "Deferral cleared for {$donor->full_name}");
    }

    /**
     * Record a donation
     */
    public function recordDonation(Request $request, Donor $donor)
    {
        $request->validate([
            'donation_date' => 'required|date',
            'blood_volume' => 'nullable|integer|min:100|max:500',
            'type' => 'required|in:whole_blood,plasma,platelets,red_cells',
            'status' => 'required|in:completed,rejected,deferred,cancelled',
            'rejection_reason' => 'nullable|string',
            'hemoglobin_before' => 'nullable|numeric',
            'notes' => 'nullable|string'
        ]);

        $donationDate = Carbon::parse($request->donation_date);
        $nextEligibleDate = $donationDate->addMonths(3);

        $donation = DonationRecord::create([
            'donor_id' => $donor->id,
            'donation_date' => $donationDate,
            'blood_volume' => $request->blood_volume ?? 450,
            'type' => $request->type,
            'status' => $request->status,
            'rejection_reason' => $request->rejection_reason,
            'next_eligible_date' => $nextEligibleDate,
            'hemoglobin_before' => $request->hemoglobin_before,
            'notes' => $request->notes
        ]);

        // Update donor's last donation date if completed
        if ($request->status === 'completed') {
            $donor->update([
                'last_donation_date' => $donationDate,
                'last_health_checkup' => now()
            ]);
        }

        return redirect()->route('admin.donors')
            ->with('success', 'Donation record saved successfully');
    }

    /**
     * Get donation statistics for a donor
     */
    public function getDonationStats(Donor $donor)
    {
        $totalDonations = DonationRecord::where('donor_id', $donor->id)
            ->where('status', 'completed')
            ->count();

        $lastYear = DonationRecord::where('donor_id', $donor->id)
            ->where('status', 'completed')
            ->where('donation_date', '>=', now()->subYear())
            ->count();

        $totalVolume = DonationRecord::where('donor_id', $donor->id)
            ->where('status', 'completed')
            ->sum('blood_volume');

        $avgHemoglobin = DonationRecord::where('donor_id', $donor->id)
            ->where('status', 'completed')
            ->whereNotNull('hemoglobin_before')
            ->avg('hemoglobin_before');

        return response()->json([
            'total_donations' => $totalDonations,
            'donations_last_year' => $lastYear,
            'total_blood_volume' => $totalVolume,
            'avg_hemoglobin' => round($avgHemoglobin, 2)
        ]);
    }

    /**
     * Dashboard showing overall donation statistics
     */
    public function dashboard()
    {
        $totalDonors = Donor::count();
        $eligibleDonors = 0;

        $donors = Donor::all();
        foreach ($donors as $donor) {
            $eligibility = $this->eligibilityService->isEligible($donor);
            if ($eligibility['eligible']) {
                $eligibleDonors++;
            }
        }

        $deferredDonors = Donor::where('is_deferred', true)->count();
        $recentDonations = DonationRecord::where('status', 'completed')
            ->where('donation_date', '>=', now()->subDays(30))
            ->count();

        $rejectedDonations = DonationRecord::where('status', 'rejected')
            ->where('donation_date', '>=', now()->subMonths(3))
            ->count();

        $bloodGroupStats = Donor::selectRaw('blood_group, COUNT(*) as total')
            ->groupBy('blood_group')
            ->get();

        return view('admin.dashboard.donation_eligibility_dashboard', compact(
            'totalDonors',
            'eligibleDonors',
            'deferredDonors',
            'recentDonations',
            'rejectedDonations',
            'bloodGroupStats'
        ));
    }
}
