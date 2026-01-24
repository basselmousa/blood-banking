<?php

namespace App\Services;

use App\Models\Donor;

class DonationRiskAssessmentService
{
    /**
     * Calculate risk score for a donor
     */
    public function calculateRiskScore(Donor $donor): int
    {
        $score = 0;

        // Age risk
        $age = $donor->date_of_birth->age;
        if ($age < 25 || $age > 60) {
            $score += 10;
        } elseif ($age < 30 || $age > 55) {
            $score += 5;
        }

        // BMI risk
        if ($donor->weight && $donor->height) {
            $heightInMeters = $donor->height / 100;
            $bmi = $donor->weight / ($heightInMeters * $heightInMeters);
            if ($bmi < 19 || $bmi > 29) {
                $score += 10;
            } elseif ($bmi < 20 || $bmi > 28) {
                $score += 5;
            }
        }

        // Hemoglobin risk
        if ($donor->hemoglobin_level) {
            $minimumHemoglobin = $donor->gender === 'female' ? 12.5 : 13.5;
            if ($donor->hemoglobin_level < $minimumHemoglobin + 1) {
                $score += 10;
            }
        }

        // Health checkup recency
        if ($donor->last_health_checkup) {
            $monthsSinceCheckup = now()->diffInMonths($donor->last_health_checkup);
            if ($monthsSinceCheckup > 6) {
                $score += 15;
            } elseif ($monthsSinceCheckup > 3) {
                $score += 5;
            }
        } else {
            $score += 20;
        }

        // Donation frequency
        if ($donor->last_donation_date) {
            $monthsSinceDonation = now()->diffInMonths($donor->last_donation_date);
            if ($monthsSinceDonation < 6) {
                $score += 5;
            }
        }

        return min($score, 100);
    }

    /**
     * Get risk category
     */
    public function getRiskCategory(Donor $donor): string
    {
        $score = $this->calculateRiskScore($donor);

        if ($score < 25) {
            return 'low';
        } elseif ($score < 50) {
            return 'medium';
        } elseif ($score < 75) {
            return 'high';
        } else {
            return 'critical';
        }
    }

    /**
     * Get risk details
     */
    public function getRiskDetails(Donor $donor): array
    {
        $score = $this->calculateRiskScore($donor);
        $category = $this->getRiskCategory($donor);
        $risks = [];

        // Age assessment
        $age = $donor->date_of_birth->age;
        if ($age < 25 || $age > 60) {
            $risks[] = "Age ($age) is outside optimal range (25-60)";
        }

        // BMI assessment
        if ($donor->weight && $donor->height) {
            $heightInMeters = $donor->height / 100;
            $bmi = round($donor->weight / ($heightInMeters * $heightInMeters), 2);
            if ($bmi < 19 || $bmi > 29) {
                $risks[] = "BMI ($bmi) is outside healthy range (19-29)";
            }
        }

        // Health checkup assessment
        if (!$donor->last_health_checkup) {
            $risks[] = "No recent health checkup on record";
        } elseif (now()->diffInMonths($donor->last_health_checkup) > 6) {
            $risks[] = "Health checkup is older than 6 months";
        }

        // Donation frequency
        if ($donor->last_donation_date && now()->diffInMonths($donor->last_donation_date) < 6) {
            $risks[] = "Recent donation (less than 6 months ago)";
        }

        return [
            'score' => $score,
            'category' => $category,
            'risks' => $risks
        ];
    }
}
