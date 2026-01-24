<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory, BelongsToTenant, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tenant_id',
        'donor_id',
        'user_id',
        'blood_type',
        'scheduled_at',
        'completed_at',
        'status',
        'notes',
        'appointment_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the donor for this appointment.
     */
    public function donor()
    {
        return $this->belongsTo(Donor::class);
    }

    /**
     * Get the staff member who recorded this appointment.
     */
    public function staff()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the tenant.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope to completed appointments.
     */
    public function scopeCompleted($query)
    {
        return $query->whereStatus('completed');
    }

    /**
     * Scope to scheduled appointments.
     */
    public function scopeScheduled($query)
    {
        return $query->whereStatus('scheduled');
    }

    /**
     * Scope to upcoming appointments.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>=', now())->whereStatus('scheduled');
    }

    /**
     * Scope to past appointments.
     */
    public function scopePast($query)
    {
        return $query->where('scheduled_at', '<', now());
    }

    /**
     * Mark appointment as completed.
     */
    public function complete()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark appointment as no-show.
     */
    public function noShow()
    {
        $this->update(['status' => 'no_show']);
    }

    /**
     * Cancel appointment.
     */
    public function cancel()
    {
        $this->update(['status' => 'cancelled']);
    }
}
