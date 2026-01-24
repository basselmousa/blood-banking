<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EligibilityTemplate extends Model
{
    use \App\Traits\BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'criteria',
        'age_range',
        'hemoglobin_levels',
        'weight_limit',
        'blood_pressure',
        'custom_questions',
        'deferral_periods',
        'medications_to_defer',
        'conditions_to_defer',
        'is_default',
        'is_active',
        'version',
    ];

    protected $casts = [
        'criteria' => 'json',
        'age_range' => 'json',
        'hemoglobin_levels' => 'json',
        'custom_questions' => 'json',
        'deferral_periods' => 'json',
        'medications_to_defer' => 'json',
        'conditions_to_defer' => 'json',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function evaluateDonor($donor)
    {
        $ineligibilityReasons = [];

        // Check age
        $ageRange = $this->age_range;
        if ($ageRange) {
            $age = $donor->getAge();
            if ($age < $ageRange['min'] || $age > $ageRange['max']) {
                $ineligibilityReasons[] = "Age must be between {$ageRange['min']} and {$ageRange['max']}";
            }
        }

        // Check weight
        if ($this->weight_limit && $donor->weight < $this->weight_limit) {
            $ineligibilityReasons[] = "Weight must be at least {$this->weight_limit} kg";
        }

        // Check hemoglobin
        if ($this->hemoglobin_levels && $donor->blood_type) {
            $minHgb = $donor->isMale() 
                ? $this->hemoglobin_levels['min_male'] 
                : $this->hemoglobin_levels['min_female'];
            
            if ($donor->hemoglobin < $minHgb) {
                $ineligibilityReasons[] = "Hemoglobin level too low (minimum: {$minHgb})";
            }
        }

        // Check blood pressure
        if ($this->blood_pressure) {
            $systolic = $donor->blood_pressure_systolic ?? 0;
            $diastolic = $donor->blood_pressure_diastolic ?? 0;
            
            if ($systolic > $this->blood_pressure['systolic_max'] || 
                $diastolic > $this->blood_pressure['diastolic_max']) {
                $ineligibilityReasons[] = "Blood pressure is too high";
            }
        }

        // Check medications
        if ($this->medications_to_defer && $donor->current_medications) {
            $medications = explode(',', $donor->current_medications);
            foreach ($medications as $med) {
                if (in_array(strtolower(trim($med)), array_map('strtolower', $this->medications_to_defer))) {
                    $ineligibilityReasons[] = "Current medications make you ineligible to donate";
                }
            }
        }

        // Check conditions
        if ($this->conditions_to_defer && $donor->medical_conditions) {
            $conditions = explode(',', $donor->medical_conditions);
            foreach ($conditions as $condition) {
                if (in_array(strtolower(trim($condition)), array_map('strtolower', $this->conditions_to_defer))) {
                    $ineligibilityReasons[] = "Medical condition makes you ineligible to donate";
                }
            }
        }

        return [
            'is_eligible' => empty($ineligibilityReasons),
            'reasons' => $ineligibilityReasons,
        ];
    }

    public function getDeferralPeriod($type)
    {
        if ($this->deferral_periods && isset($this->deferral_periods[$type])) {
            return $this->deferral_periods[$type];
        }

        return 0;
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLatestVersion($query)
    {
        return $query->orderBy('version', 'desc')->limit(1);
    }

    public function clone()
    {
        $newTemplate = $this->replicate();
        $newTemplate->version = ($this->version ?? 1) + 1;
        $newTemplate->save();

        return $newTemplate;
    }
}
