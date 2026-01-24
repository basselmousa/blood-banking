<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tenant_id',
        'stripe_id',
        'stripe_status',
        'plan',
        'quantity',
        'trial_ends_at',
        'ends_at',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'trial_ends_at' => 'datetime',
        'ends_at' => 'datetime',
        'metadata' => 'json',
    ];

    /**
     * Get the tenant that owns the subscription.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Determine if the subscription is active.
     */
    public function active()
    {
        return $this->whereIn('stripe_status', ['active', 'trialing'])->orWhereNull('ends_at');
    }

    /**
     * Determine if the subscription is on trial.
     */
    public function onTrial()
    {
        return !is_null($this->trial_ends_at) && $this->trial_ends_at->isFuture();
    }

    /**
     * Determine if the subscription is cancelled.
     */
    public function cancelled()
    {
        return !is_null($this->ends_at);
    }

    /**
     * Upgrade the subscription to a new plan.
     */
    public function upgrade($newPlan)
    {
        $this->plan = $newPlan;
        $this->save();

        // Update tenant plan
        $this->tenant->update(['plan' => $newPlan]);
    }

    /**
     * Cancel the subscription.
     */
    public function cancel()
    {
        $this->ends_at = now();
        $this->save();
    }
}
