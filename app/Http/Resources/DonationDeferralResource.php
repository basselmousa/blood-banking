<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DonationDeferralResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'donor_id' => $this->donor_id,
            'donor' => [
                'id' => $this->donor->id,
                'full_name' => $this->donor->full_name,
                'email' => $this->donor->email,
                'blood_group' => $this->donor->blood_group,
            ],
            'deferral_type' => $this->deferral_type,
            'reason' => $this->reason,
            'description' => $this->description,
            'deferral_date' => $this->deferral_date->toIso8601String(),
            'eligible_after' => $this->eligible_after?->toIso8601String(),
            'is_active' => (bool) $this->is_active,
            'admin_notes' => $this->admin_notes,
            'days_remaining' => $this->eligible_after ? max(0, now()->diffInDays($this->eligible_after, false)) : null,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
