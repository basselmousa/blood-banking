<?php

namespace App\Helpers;

use App\Models\Donor;
use Carbon\Carbon;

class BloodCompatibilityHelper
{
    /**
     * Compatible donors for a patient's blood type
     * O- is universal donor, AB+ is universal recipient
     */
    public static function getCompatibleDonors(string $bloodGroup, string $rhesus = '+'): array
    {
        $compatibility = [
            'O+' => ['O+', 'O-'],
            'O-' => ['O-'],
            'A+' => ['A+', 'A-', 'O+', 'O-'],
            'A-' => ['A-', 'O-'],
            'B+' => ['B+', 'B-', 'O+', 'O-'],
            'B-' => ['B-', 'O-'],
            'AB+' => ['AB+', 'AB-', 'A+', 'A-', 'B+', 'B-', 'O+', 'O-'],
            'AB-' => ['AB-', 'A-', 'B-', 'O-'],
        ];

        $key = $bloodGroup . $rhesus;
        return $compatibility[$key] ?? [];
    }

    /**
     * Get all blood groups that can receive from given donor
     */
    public static function getRecipientBloodGroups(string $donorBlood, string $donorRhesus = '+'): array
    {
        $recipients = [
            'O+' => ['O+', 'A+', 'B+', 'AB+'],
            'O-' => ['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'],
            'A+' => ['A+', 'AB+'],
            'A-' => ['A+', 'A-', 'AB+', 'AB-'],
            'B+' => ['B+', 'AB+'],
            'B-' => ['B+', 'B-', 'AB+', 'AB-'],
            'AB+' => ['AB+'],
            'AB-' => ['AB+', 'AB-'],
        ];

        $key = $donorBlood . $donorRhesus;
        return $recipients[$key] ?? [];
    }

    /**
     * Check if donor's blood is compatible with patient
     */
    public static function isCompatible(Donor $donor, string $patientBlood, string $patientRhesus = '+'): bool
    {
        $patientKey = $patientBlood . $patientRhesus;
        $recipientGroups = self::getRecipientBloodGroups($donor->blood_group);

        return in_array($patientKey, $recipientGroups);
    }

    /**
     * Get percentage of population with compatible blood type
     */
    public static function getCompatibilityPercentage(string $bloodGroup, string $rhesus = '+'): float
    {
        // Approximate percentages based on global statistics
        $percentages = [
            'O+' => 37.0,
            'O-' => 6.0,
            'A+' => 35.0,
            'A-' => 6.0,
            'B+' => 8.0,
            'B-' => 1.0,
            'AB+' => 4.0,
            'AB-' => 1.0,
        ];

        $key = $bloodGroup . $rhesus;
        return $percentages[$key] ?? 0;
    }
}
