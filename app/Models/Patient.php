<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $dates = ['date_of_birth'];
    protected $casts = [
        'special_requirements' => 'array',
        'transfusion_history' => 'array',
    ];

    /**
     * Get patient's age
     */
    public function getAgeAttribute()
    {
        return $this->date_of_birth->age;
    }

    /**
     * Get complete blood type with rhesus
     */
    public function getCompleteBloodTypeAttribute()
    {
        $rhesus = $this->rhesus_factor ?? '+';
        return $this->blood_group . $rhesus;
    }

    /**
     * Scopes for querying
     */

    /**
     * Get actual patients (not home donors)
     */
    public function scopePatients($query)
    {
        return $query->where('type', 'patient');
    }

    /**
     * Get home donors
     */
    public function scopeHomeDonors($query)
    {
        return $query->where('type', 'donor');
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
     * By type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}
