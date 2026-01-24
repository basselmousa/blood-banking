<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonorPortalSettings extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tenant_id',
        'enabled',
        'allow_appointment_booking',
        'allow_eligibility_self_check',
        'show_inventory_status',
        'send_appointment_reminders',
        'send_donation_records',
        'appointment_reminder_hours',
        'custom_fields',
        'features',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'enabled' => 'boolean',
        'allow_appointment_booking' => 'boolean',
        'allow_eligibility_self_check' => 'boolean',
        'show_inventory_status' => 'boolean',
        'send_appointment_reminders' => 'boolean',
        'send_donation_records' => 'boolean',
        'custom_fields' => 'json',
        'features' => 'json',
    ];

    /**
     * Get the tenant.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Check if a feature is enabled.
     */
    public function hasFeature($feature)
    {
        $features = $this->features ?? [];
        return in_array($feature, $features);
    }

    /**
     * Enable a feature.
     */
    public function enableFeature($feature)
    {
        $features = $this->features ?? [];
        if (!in_array($feature, $features)) {
            $features[] = $feature;
            $this->update(['features' => $features]);
        }
    }

    /**
     * Disable a feature.
     */
    public function disableFeature($feature)
    {
        $features = $this->features ?? [];
        $this->update(['features' => array_diff($features, [$feature])]);
    }
}
