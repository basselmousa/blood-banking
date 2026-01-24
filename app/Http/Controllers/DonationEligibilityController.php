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
    public function listEligible(\App\Http\Requests\ListEligibleDonorsRequest $request)
    {
        $validated = $request->validated();

        $donors = Donor::byBloodGroup($validated['blood_group'])
            ->byCity($validated['city'] ?? null)
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
    public function defer(\App\Http\Requests\DeferDonorRequest $request, Donor $donor)
    {
        $validated = $request->validated();

        $eligibleAfter = null;
        if ($request->eligible_after) {
            $eligibleAfter = Carbon::parse($request->eligible_after);
        } elseif ($request->deferral_type === 'temporary') {
            $eligibleAfter = now()->addMonths(1);
        }

        $this->eligibilityService->deferDonor(
            $donor,
            $validated['reason'],
            $validated['description'],
            $eligibleAfter,
            $validated['deferral_type']
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
    public function recordDonation(\App\Http\Requests\RecordDonationRequest $request, Donor $donor)
    {
        $validated = $request->validated();

        $donationDate = Carbon::parse($validated['donation_date']);
        $nextEligibleDate = $donationDate->addMonths(3);

        $donation = DonationRecord::create([
            'donor_id' => $donor->id,
            'donation_date' => $donationDate,
            'blood_volume' => $validated['blood_volume'] ?? 450,
            'type' => $validated['type'],
            'status' => $validated['status'],
            'rejection_reason' => $validated['rejection_reason'] ?? null,
            'next_eligible_date' => $nextEligibleDate,
            'hemoglobin_before' => $validated['hemoglobin_before'] ?? null,
            'notes' => $validated['notes'] ?? null
        ]);

        // Update donor's last donation date if completed
        if ($validated['status'] === 'completed') {
            $donor->update(['last_donation_date' => $donationDate]);
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
