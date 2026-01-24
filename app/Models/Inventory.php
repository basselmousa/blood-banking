<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory, BelongsToTenant;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inventory';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tenant_id',
        'blood_type',
        'component_type',
        'quantity',
        'quantity_unit',
        'expiration_date',
        'storage_location',
        'critical_level',
        'maximum_level',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'expiration_date' => 'date',
    ];

    /**
     * Get transactions for this inventory.
     */
    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    /**
     * Get the tenant.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Check if inventory is below critical level.
     */
    public function isBelowCritical()
    {
        return $this->quantity <= $this->critical_level;
    }

    /**
     * Check if inventory is above maximum level.
     */
    public function isAboveMaximum()
    {
        return $this->quantity >= $this->maximum_level;
    }

    /**
     * Check if inventory is expired.
     */
    public function isExpired()
    {
        return $this->expiration_date && $this->expiration_date->isPast();
    }

    /**
     * Check if inventory is expiring soon (within 7 days).
     */
    public function isExpiringSoon()
    {
        return $this->expiration_date && 
               $this->expiration_date->isBetween(now(), now()->addDays(7));
    }

    /**
     * Add to inventory.
     */
    public function addQuantity($amount, $reason = null)
    {
        $before = $this->quantity;
        $this->quantity += $amount;
        $this->save();

        InventoryTransaction::create([
            'tenant_id' => $this->tenant_id,
            'inventory_id' => $this->id,
            'transaction_type' => 'donation',
            'quantity_change' => $amount,
            'quantity_before' => $before,
            'quantity_after' => $this->quantity,
            'reason' => $reason,
        ]);

        return $this;
    }

    /**
     * Remove from inventory.
     */
    public function removeQuantity($amount, $reason = null)
    {
        $before = $this->quantity;
        $this->quantity -= $amount;
        $this->save();

        InventoryTransaction::create([
            'tenant_id' => $this->tenant_id,
            'inventory_id' => $this->id,
            'transaction_type' => 'transfusion',
            'quantity_change' => -$amount,
            'quantity_before' => $before,
            'quantity_after' => $this->quantity,
            'reason' => $reason,
        ]);

        return $this;
    }
}
