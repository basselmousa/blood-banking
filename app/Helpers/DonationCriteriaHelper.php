<?php

namespace App\Helpers;

use Carbon\Carbon;

class DonationCriteriaHelper
{
    /**
     * Minimum age for donation
     */
    const MIN_AGE = 18;

    /**
     * Maximum age for donation
     */
    const MAX_AGE = 65;

    /**
     * Minimum weight in kg
     */
    const MIN_WEIGHT = 50;

    /**
     * Minimum height in cm
     */
    const MIN_HEIGHT = 100;

    /**
     * Minimum hemoglobin for females (g/dL)
     */
    const MIN_HEMOGLOBIN_FEMALE = 12.5;

    /**
     * Minimum hemoglobin for males (g/dL)
     */
    const MIN_HEMOGLOBIN_MALE = 13.5;

    /**
     * Minimum gap between donations (months)
     */
    const MIN_DONATION_GAP_MONTHS = 3;

    /**
     * Recommended blood volume per donation (ml)
     */
    const DONATION_VOLUME = 450;

    /**
     * Healthy BMI range
     */
    const BMI_MIN = 18.5;
    const BMI_MAX = 29.9;

    /**
     * Get minimum hemoglobin by gender
     */
    public static function getMinHemoglobin(string $gender): float
    {
        return $gender === 'female' ? self::MIN_HEMOGLOBIN_FEMALE : self::MIN_HEMOGLOBIN_MALE;
    }

    /**
     * Check if age is within donation range
     */
    public static function isAgeValid(int $age): bool
    {
        return $age >= self::MIN_AGE && $age <= self::MAX_AGE;
    }

    /**
     * Check if weight is valid
     */
    public static function isWeightValid(?int $weight): bool
    {
        return $weight === null || $weight >= self::MIN_WEIGHT;
    }

    /**
     * Check if height is valid
     */
    public static function isHeightValid(?int $height): bool
    {
        return $height === null || $height >= self::MIN_HEIGHT;
    }

    /**
     * Calculate BMI
     */
    public static function calculateBMI(int $weightKg, int $heightCm): float
    {
        $heightInMeters = $heightCm / 100;
        return $weightKg / ($heightInMeters * $heightInMeters);
    }

    /**
     * Check if BMI is in healthy range
     */
    public static function isBMIHealthy(float $bmi): bool
    {
        return $bmi >= self::BMI_MIN && $bmi <= self::BMI_MAX;
    }

    /**
     * Check if hemoglobin is sufficient
     */
    public static function isHemoglobinSufficient(float $hemoglobin, string $gender): bool
    {
        $minimum = self::getMinHemoglobin($gender);
        return $hemoglobin >= $minimum;
    }

    /**
     * Check if enough time has passed since last donation
     */
    public static function isDonationGapSufficient(?Carbon $lastDonation): bool
    {
        if ($lastDonation === null) {
            return true;
        }

        return now()->diffInMonths($lastDonation) >= self::MIN_DONATION_GAP_MONTHS;
    }

    /**
     * Get next eligible donation date
     */
    public static function getNextEligibleDate(?Carbon $lastDonation): Carbon
    {
        if ($lastDonation === null) {
            return now();
        }

        return $lastDonation->copy()->addMonths(self::MIN_DONATION_GAP_MONTHS);
    }

    /**
     * Get days until eligible for next donation
     */
    public static function getDaysUntilEligible(?Carbon $lastDonation): int
    {
        if ($lastDonation === null) {
            return 0;
        }

        $nextEligible = self::getNextEligibleDate($lastDonation);
        $daysRemaining = now()->diffInDays($nextEligible, false);

        return max(0, $daysRemaining);
    }

    /**
     * Get disqualifying health conditions
     */
    public static function getDisqualifyingConditions(): array
    {
        return [
            'hiv',
            'hepatitis_b',
            'hepatitis_c',
            'syphilis',
            'malaria_active',
            'tuberculosis_active',
            'heart_disease',
            'kidney_disease',
            'liver_disease',
            'cancer',
        ];
    }

    /**
     * Get temporary deferral conditions and their duration
     */
    public static function getTemporaryDeferrals(): array
    {
        return [
            'recent_tattoo' => 6,          // months
            'recent_piercing' => 6,        // months
            'recent_vaccination' => 2,     // weeks (some vaccines)
            'recent_surgery' => 4,         // weeks
            'recent_dental_work' => 1,     // week
            'malaria_exposure' => 12,      // months
            'blood_transfusion' => 3,      // months
            'pregnancy' => 9,              // months
            'breastfeeding' => 3,          // months
            'recent_infection' => 2,       // weeks
            'recent_antibiotics' => 1,     // week after completion
        ];
    }

    /**
     * Check for disqualifying condition
     */
    public static function hasDisqualifyingCondition(string $conditionString): bool
    {
        $disqualifying = self::getDisqualifyingConditions();
        $conditions = array_map('strtolower', array_map('trim', explode(',', $conditionString)));

        foreach ($disqualifying as $condition) {
            if (in_array($condition, $conditions)) {
                return true;
            }
        }

        return false;
    }
}
