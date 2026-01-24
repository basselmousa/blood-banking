<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonationRecord extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $dates = ['donation_date', 'next_eligible_date'];

    public function donor()
    {
        return $this->belongsTo(Donor::class);
    }

    /**
     * Get completed donations
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Get rejected donations
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Get recent donations
     */
    public function scopeRecent($query, $months = 12)
    {
        return $query->where('donation_date', '>=', now()->subMonths($months));
    }
}
