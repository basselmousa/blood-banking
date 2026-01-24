<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donor extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $dates = ['last_donation_date', 'date_of_birth', 'last_health_checkup', 'deferred_until'];
    protected $casts = [
        'health_conditions' => 'array',
    ];

    /**
     * Relationships
     */
    public function donationRecords()
    {
        return $this->hasMany(DonationRecord::class);
    }

    public function deferrals()
    {
        return $this->hasMany(DonationDeferral::class);
    }

    /**
     * Get donor's age
     */
    public function getAgeAttribute()
    {
        return $this->date_of_birth->age;
    }

    /**
     * Calculate BMI
     */
    public function getBmiAttribute()
    {
        if (!$this->weight || !$this->height) {
            return null;
        }
        $heightInMeters = $this->height / 100;
        return round($this->weight / ($heightInMeters * $heightInMeters), 2);
    }

    /**
     * Get complete blood type with rhesus
     */
    public function getCompleteBloodTypeAttribute()
    {
        return $this->blood_group . '+';
    }

    /**
     * Scopes for querying
     */

    /**
     * Eligible donors only
     */
    public function scopeEligible($query)
    {
        return $query->where('is_deferred', false)
            ->where(function ($q) {
                $q->whereNull('last_donation_date')
                  ->orWhereDate('last_donation_date', '<=', now()->subMonths(3));
            });
    }

    /**
     * Not currently deferred
     */
    public function scopeNotDeferred($query)
    {
        return $query->where('is_deferred', false);
    }

    /**
     * Within donation gap (3+ months since last donation)
     */
    public function scopeWithinDonationGap($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('last_donation_date')
              ->orWhereDate('last_donation_date', '<=', now()->subMonths(3));
        });
    }

    /**
     * Recent donors (last 12 months)
     */
    public function scopeRecentDonors($query)
    {
        return $query->whereNotNull('last_donation_date')
            ->whereDate('last_donation_date', '>=', now()->subMonths(12));
    }

    /**
     * Healthy donors (with good health metrics)
     */
    public function scopeHealthy($query)
    {
        return $query->where(function ($q) {
            $minHemoglobin = 12.5;
            $q->whereNull('hemoglobin_level')
              ->orWhere('hemoglobin_level', '>=', $minHemoglobin);
        });
    }

    /**
     * By blood group
     */
    public function scopeByBloodGroup($query, $bloodGroup)
    {
        return $query->where('blood_group', $bloodGroup);
    }

    /**
     * By city
     */
    public function scopeByCity($query, $city)
    {
        if ($city !== 'all') {
            return $query->where('city', $city);
        }
        return $query;
    }

    /**
     * Active donors (not deferred, within donation gap)
     */
    public function scopeActive($query)
    {
        return $query->notDeferred()->withinDonationGap();
    }
}
