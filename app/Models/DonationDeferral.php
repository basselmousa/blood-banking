<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonationDeferral extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $dates = ['deferral_date', 'eligible_after'];

    public function donor()
    {
        return $this->belongsTo(Donor::class);
    }

    /**
     * Get active deferrals
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get temporary deferrals
     */
    public function scopeTemporary($query)
    {
        return $query->where('deferral_type', 'temporary');
    }

    /**
     * Get permanent deferrals
     */
    public function scopePermanent($query)
    {
        return $query->where('deferral_type', 'permanent');
    }

    /**
     * Check if deferral is still active
     */
    public function isStillActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->deferral_type === 'permanent') {
            return true;
        }

        if ($this->eligible_after && now()->isAfter($this->eligible_after)) {
            return false;
        }

        return true;
    }
}
