<?php

namespace App\Services;

use App\Models\Donor;
use App\Models\DonationDeferral;
use Carbon\Carbon;

class DonationEligibilityService
{
    /**
     * Check if a donor is eligible to donate
     */
    public function isEligible(Donor $donor): array
    {
        $issues = [];

        // Age check (18-65 years recommended)
        $ageCheck = $this->checkAge($donor);
        if (!$ageCheck['eligible']) {
            $issues[] = $ageCheck;
        }

        // BMI check
        $bmiCheck = $this->checkBMI($donor);
        if (!$bmiCheck['eligible']) {
            $issues[] = $bmiCheck;
        }

        // Hemoglobin level
        $hemoglobinCheck = $this->checkHemoglobin($donor);
        if (!$hemoglobinCheck['eligible']) {
            $issues[] = $hemoglobinCheck;
        }

        // Weight check (minimum 50kg)
        $weightCheck = $this->checkWeight($donor);
        if (!$weightCheck['eligible']) {
            $issues[] = $weightCheck;
        }

        // Last donation date check
        $donationGapCheck = $this->checkDonationGap($donor);
        if (!$donationGapCheck['eligible']) {
            $issues[] = $donationGapCheck;
        }

        // Active deferral check
        $deferralCheck = $this->checkActiveDeferral($donor);
        if (!$deferralCheck['eligible']) {
            $issues[] = $deferralCheck;
        }

        // Health conditions check
        $healthCheck = $this->checkHealthConditions($donor);
        if (!$healthCheck['eligible']) {
            $issues[] = $healthCheck;
        }

        return [
            'eligible' => empty($issues),
            'issues' => $issues,
            'next_eligible_date' => $this->getNextEligibleDate($donor),
            'risk_level' => $this->calculateRiskLevel($donor, $issues)
        ];
    }

    /**
     * Check if donor is within appropriate age range
     */
    private function checkAge(Donor $donor): array
    {
        $age = $donor->date_of_birth->age;
        
        if ($age < 18) {
            return [
                'eligible' => false,
                'reason' => 'age_too_young',
                'message' => "Donor must be at least 18 years old. Current age: $age"
            ];
        }

        if ($age > 65) {
            return [
                'eligible' => false,
                'reason' => 'age_too_old',
                'message' => "Donors over 65 are not recommended. Current age: $age"
            ];
        }

        return ['eligible' => true];
    }

    /**
     * Check BMI (18.5 - 29.9 is healthy range)
     */
    private function checkBMI(Donor $donor): array
    {
        if (!$donor->weight || !$donor->height) {
            return ['eligible' => true]; // Can't check without data
        }

        $heightInMeters = $donor->height / 100;
        $bmi = $donor->weight / ($heightInMeters * $heightInMeters);

        if ($bmi < 18.5) {
            return [
                'eligible' => false,
                'reason' => 'bmi_too_low',
                'message' => "BMI is too low ($bmi). Minimum is 18.5"
            ];
        }

        if ($bmi > 29.9) {
            return [
                'eligible' => false,
                'reason' => 'bmi_too_high',
                'message' => "BMI is too high ($bmi). Maximum is 29.9"
            ];
        }

        return ['eligible' => true];
    }

    /**
     * Check hemoglobin levels (gender-specific)
     */
    private function checkHemoglobin(Donor $donor): array
    {
        if (!$donor->hemoglobin_level) {
            return ['eligible' => true]; // Need recent checkup
        }

        $minimumHemoglobin = $donor->gender === 'female' ? 12.5 : 13.5;

        if ($donor->hemoglobin_level < $minimumHemoglobin) {
            return [
                'eligible' => false,
                'reason' => 'hemoglobin_low',
                'message' => "Hemoglobin level ({$donor->hemoglobin_level} g/dL) is below minimum of $minimumHemoglobin"
            ];
        }

        return ['eligible' => true];
    }

    /**
     * Check minimum weight requirement
     */
    private function checkWeight(Donor $donor): array
    {
        if (!$donor->weight) {
            return ['eligible' => true];
        }

        if ($donor->weight < 50) {
            return [
                'eligible' => false,
                'reason' => 'weight_too_low',
                'message' => "Weight ({$donor->weight}kg) is below minimum of 50kg"
            ];
        }

        return ['eligible' => true];
    }

    /**
     * Check minimum gap between donations (3-6 months)
     */
    private function checkDonationGap(Donor $donor): array
    {
        if (!$donor->last_donation_date) {
            return ['eligible' => true];
        }

        $minimumGapMonths = 3;
        $nextEligibleDate = $donor->last_donation_date->addMonths($minimumGapMonths);

        if (now()->lessThan($nextEligibleDate)) {
            $daysUntilEligible = now()->diffInDays($nextEligibleDate);
            return [
                'eligible' => false,
                'reason' => 'donation_gap_insufficient',
                'message' => "Must wait $daysUntilEligible more days (until $nextEligibleDate->format('Y-m-d'))",
                'next_eligible_date' => $nextEligibleDate
            ];
        }

        return ['eligible' => true];
    }

    /**
     * Check if donor has active deferral
     */
    private function checkActiveDeferral(Donor $donor): array
    {
        $activeDeferral = DonationDeferral::where('donor_id', $donor->id)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('eligible_after')
                      ->orWhereDate('eligible_after', '>', now());
            })
            ->first();

        if ($activeDeferral) {
            return [
                'eligible' => false,
                'reason' => 'active_deferral',
                'message' => $activeDeferral->reason . ': ' . $activeDeferral->description,
                'deferral_until' => $activeDeferral->eligible_after
            ];
        }

        return ['eligible' => true];
    }

    /**
     * Check health conditions and diseases
     */
    private function checkHealthConditions(Donor $donor): array
    {
        $disqualifyingDiseases = [
            'hiv',
            'hepatitis_b',
            'hepatitis_c',
            'syphilis',
            'malaria_active',
            'tuberculosis_active'
        ];

        $diseases = [];
        if ($donor->diseases) {
            $diseases = array_map('strtolower', explode(',', $donor->diseases));
        }

        foreach ($disqualifyingDiseases as $disease) {
            if (in_array($disease, $diseases)) {
                return [
                    'eligible' => false,
                    'reason' => 'disqualifying_disease',
                    'message' => "Donor has disqualifying health condition: $disease"
                ];
            }
        }

        return ['eligible' => true];
    }

    /**
     * Get next eligible donation date
     */
    public function getNextEligibleDate(Donor $donor): ?Carbon
    {
        if (!$donor->last_donation_date) {
            return now();
        }

        return $donor->last_donation_date->addMonths(3);
    }

    /**
     * Calculate risk level
     */
    private function calculateRiskLevel(Donor $donor, array $issues): string
    {
        if (count($issues) === 0) {
            return 'low';
        }

        if (count($issues) <= 1) {
            return 'medium';
        }

        return 'high';
    }

    /**
     * Defer a donor
     */
    public function deferDonor(Donor $donor, string $reason, string $description, ?Carbon $eligibleAfter = null, string $type = 'temporary')
    {
        DonationDeferral::create([
            'donor_id' => $donor->id,
            'reason' => $reason,
            'description' => $description,
            'deferral_date' => now(),
            'eligible_after' => $eligibleAfter,
            'deferral_type' => $type,
            'is_active' => true
        ]);

        $donor->update([
            'is_deferred' => true,
            'deferred_until' => $eligibleAfter,
            'deferral_reason' => $reason
        ]);
    }

    /**
     * Clear deferral for a donor
     */
    public function clearDeferral(Donor $donor)
    {
        DonationDeferral::where('donor_id', $donor->id)
            ->update(['is_active' => false]);

        $donor->update([
            'is_deferred' => false,
            'deferred_until' => null,
            'deferral_reason' => null
        ]);
    }
}
