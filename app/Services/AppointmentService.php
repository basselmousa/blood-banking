<?php

namespace App\Services;

use App\Models\Appointment;
use Carbon\Carbon;

class AppointmentService
{
    /**
     * Create a new appointment.
     */
    public function createAppointment($data)
    {
        $appointment = Appointment::create([
            'tenant_id' => $data['tenant_id'],
            'donor_id' => $data['donor_id'],
            'user_id' => $data['user_id'] ?? null,
            'blood_type' => $data['blood_type'] ?? null,
            'scheduled_at' => $data['scheduled_at'],
            'appointment_type' => $data['appointment_type'] ?? 'donation',
            'notes' => $data['notes'] ?? null,
        ]);

        // Fire event for notifications
        event(new \App\Events\AppointmentCreated($appointment));

        return $appointment;
    }

    /**
     * Get available time slots.
     */
    public function getAvailableSlots($tenantId, $date, $slotDuration = 30)
    {
        $startTime = Carbon::parse($date)->startOfDay()->addHours(9); // 9 AM
        $endTime = Carbon::parse($date)->startOfDay()->addHours(17); // 5 PM

        $slots = [];
        $current = $startTime->copy();

        while ($current->lessThan($endTime)) {
            // Check if slot is available (not fully booked)
            $appointmentsInSlot = Appointment::where('tenant_id', $tenantId)
                ->whereDate('scheduled_at', $date)
                ->whereBetween('scheduled_at', [
                    $current->copy(),
                    $current->copy()->addMinutes($slotDuration),
                ])
                ->whereIn('status', ['scheduled', 'completed'])
                ->count();

            if ($appointmentsInSlot < 5) { // Max 5 per slot
                $slots[] = [
                    'time' => $current->format('H:i'),
                    'datetime' => $current->toIso8601String(),
                    'available' => true,
                ];
            }

            $current->addMinutes($slotDuration);
        }

        return $slots;
    }

    /**
     * Get upcoming appointments for a donor.
     */
    public function getDonorAppointments($donorId, $limit = 10)
    {
        return Appointment::where('donor_id', $donorId)
            ->upcoming()
            ->orderBy('scheduled_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get appointment statistics.
     */
    public function getStatistics($tenantId, $period = 'month')
    {
        $startDate = match($period) {
            'week' => now()->subDays(7),
            'month' => now()->subDays(30),
            'quarter' => now()->subDays(90),
            'year' => now()->subDays(365),
            default => now()->subDays(30),
        };

        return [
            'total' => Appointment::where('tenant_id', $tenantId)
                ->whereBetween('scheduled_at', [$startDate, now()])
                ->count(),
            'completed' => Appointment::where('tenant_id', $tenantId)
                ->completed()
                ->whereBetween('completed_at', [$startDate, now()])
                ->count(),
            'scheduled' => Appointment::where('tenant_id', $tenantId)
                ->scheduled()
                ->whereBetween('scheduled_at', [$startDate, now()])
                ->count(),
            'cancelled' => Appointment::where('tenant_id', $tenantId)
                ->where('status', 'cancelled')
                ->whereBetween('updated_at', [$startDate, now()])
                ->count(),
            'no_show' => Appointment::where('tenant_id', $tenantId)
                ->where('status', 'no_show')
                ->whereBetween('scheduled_at', [$startDate, now()])
                ->count(),
        ];
    }

    /**
     * Send appointment reminder.
     */
    public function sendReminder(Appointment $appointment)
    {
        // Send email/SMS reminder
        \Mail::send(new \App\Mail\AppointmentReminder($appointment));
    }

    /**
     * Cancel appointment.
     */
    public function cancel(Appointment $appointment, $reason = null)
    {
        $appointment->cancel();
        event(new \App\Events\AppointmentCancelled($appointment));
    }

    /**
     * Mark appointment as completed.
     */
    public function complete(Appointment $appointment, $notes = null)
    {
        $appointment->complete();
        if ($notes) {
            $appointment->update(['notes' => $notes]);
        }
        event(new \App\Events\AppointmentCompleted($appointment));
    }
}
