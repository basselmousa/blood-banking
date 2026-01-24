<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DonorEligibilityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'blood_group' => $this->blood_group,
            'age' => $this->age,
            'weight' => $this->weight,
            'height' => $this->height,
            'bmi' => $this->bmi,
            'hemoglobin_level' => $this->hemoglobin_level,
            'blood_pressure' => $this->blood_pressure,
            'city' => $this->city,
            'country' => $this->country,
            'last_donation_date' => $this->last_donation_date?->toIso8601String(),
            'is_deferred' => (bool) $this->is_deferred,
            'deferred_until' => $this->deferred_until?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
