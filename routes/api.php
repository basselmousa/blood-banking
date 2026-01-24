<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Donation Eligibility API Routes
Route::middleware('auth:sanctum')->group(function () {
    // Check if donor is eligible
    Route::get('/donors/{donor}/eligibility', function (Request $request, \App\Models\Donor $donor) {
        $service = new \App\Services\DonationEligibilityService();
        $riskService = new \App\Services\DonationRiskAssessmentService();
        
        $eligibility = $service->isEligible($donor);
        $riskDetails = $riskService->getRiskDetails($donor);
        
        return response()->json([
            'donor_id' => $donor->id,
            'full_name' => $donor->full_name,
            'blood_group' => $donor->blood_group,
            'eligibility' => $eligibility,
            'risk_assessment' => $riskDetails,
        ]);
    });

    // Get compatible donors for a patient
    Route::get('/patients/{patient}/compatible-donors', function (Request $request, \App\Models\Patient $patient) {
        $donors = \App\Models\Donor::byBloodGroup($patient->blood_group)
            ->active()
            ->get();

        $compatible = [];
        $service = new \App\Services\DonationEligibilityService();
        
        foreach ($donors as $donor) {
            $eligibility = $service->isEligible($donor);
            if ($eligibility['eligible']) {
                $compatible[] = [
                    'id' => $donor->id,
                    'full_name' => $donor->full_name,
                    'email' => $donor->email,
                    'phone_number' => $donor->phone_number,
                    'city' => $donor->city,
                    'next_eligible_date' => $eligibility['next_eligible_date'],
                ];
            }
        }

        return response()->json([
            'patient_id' => $patient->id,
            'patient_blood_group' => $patient->blood_group,
            'compatible_donors_count' => count($compatible),
            'compatible_donors' => $compatible,
        ]);
    });

    // Donor statistics
    Route::get('/donors/{donor}/statistics', function (\App\Models\Donor $donor) {
        $donations = $donor->donationRecords()
            ->where('status', 'completed')
            ->get();

        return response()->json([
            'donor_id' => $donor->id,
            'full_name' => $donor->full_name,
            'total_donations' => $donations->count(),
            'total_blood_volume' => $donations->sum('blood_volume'),
            'last_donation' => $donor->last_donation_date,
            'registration_date' => $donor->created_at,
            'age' => $donor->age,
            'bmi' => $donor->bmi,
            'hemoglobin_level' => $donor->hemoglobin_level,
        ]);
    });

    // List eligible donors by blood group and city
    Route::get('/eligible-donors', function (Request $request) {
        $request->validate([
            'blood_group' => 'required|string',
            'city' => 'nullable|string',
        ]);

        $query = \App\Models\Donor::byBloodGroup($request->blood_group)
            ->active();

        if ($request->city && $request->city !== 'all') {
            $query->byCity($request->city);
        }

        $donors = $query->get();
        $service = new \App\Services\DonationEligibilityService();

        $eligible = $donors->filter(function ($donor) use ($service) {
            return $service->isEligible($donor)['eligible'];
        })->map(function ($donor) {
            return [
                'id' => $donor->id,
                'full_name' => $donor->full_name,
                'email' => $donor->email,
                'phone' => $donor->phone_number,
                'city' => $donor->city,
                'age' => $donor->age,
            ];
        });

        return response()->json([
            'blood_group' => $request->blood_group,
            'city' => $request->city ?? 'all',
            'eligible_donors_count' => $eligible->count(),
            'donors' => $eligible->values(),
        ]);
    });

    // Deferred donors list
    Route::get('/deferred-donors', function () {
        $deferrals = \App\Models\DonationDeferral::where('is_active', true)
            ->with('donor')
            ->orderBy('eligible_after', 'asc')
            ->get()
            ->map(function ($deferral) {
                return [
                    'donor_id' => $deferral->donor_id,
                    'full_name' => $deferral->donor->full_name,
                    'reason' => $deferral->reason,
                    'type' => $deferral->deferral_type,
                    'deferred_until' => $deferral->eligible_after,
                ];
            });

        return response()->json([
            'deferred_donors_count' => $deferrals->count(),
            'deferrals' => $deferrals,
        ]);
    });
});

