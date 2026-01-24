<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Donor;
use App\Models\Tenant;
use App\Services\AppointmentService;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    protected $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    /**
     * Get available appointment slots.
     */
    public function availableSlots(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

        $tenant = auth()->user()->tenant;
        $slots = $this->appointmentService->getAvailableSlots($tenant->id, $validated['date']);

        return response()->json(['slots' => $slots]);
    }

    /**
     * Book an appointment (donor self-service).
     */
    public function book(Request $request)
    {
        $validated = $request->validate([
            'scheduled_at' => 'required|date_format:Y-m-d H:i|after:now',
            'notes' => 'nullable|string',
        ]);

        $donor = Donor::where('tenant_id', auth()->user()->tenant_id)
            ->where('id', auth()->user()->id)
            ->firstOrFail();

        $appointment = $this->appointmentService->createAppointment([
            'tenant_id' => auth()->user()->tenant_id,
            'donor_id' => $donor->id,
            'scheduled_at' => $validated['scheduled_at'],
            'appointment_type' => 'donation',
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'message' => 'Appointment booked successfully',
            'appointment' => $appointment,
        ]);
    }

    /**
     * List appointments (admin).
     */
    public function index(Request $request)
    {
        $tenant = auth()->user()->tenant;
        
        $query = Appointment::where('tenant_id', $tenant->id);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('date')) {
            $query->whereDate('scheduled_at', $request->date);
        }

        $appointments = $query->paginate(20);

        return view('appointments.index', compact('appointments'));
    }

    /**
     * Show appointment details.
     */
    public function show(Appointment $appointment)
    {
        if ($appointment->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        return view('appointments.show', compact('appointment'));
    }

    /**
     * Update appointment.
     */
    public function update(Request $request, Appointment $appointment)
    {
        if ($appointment->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $validated = $request->validate([
            'scheduled_at' => 'required|date_format:Y-m-d H:i',
            'notes' => 'nullable|string',
            'status' => 'required|in:scheduled,completed,cancelled,no_show',
        ]);

        $appointment->update($validated);

        return response()->json([
            'message' => 'Appointment updated successfully',
            'appointment' => $appointment,
        ]);
    }

    /**
     * Cancel appointment.
     */
    public function cancel(Request $request, Appointment $appointment)
    {
        if ($appointment->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $this->appointmentService->cancel($appointment, $request->reason);

        return response()->json(['message' => 'Appointment cancelled successfully']);
    }

    /**
     * Complete appointment.
     */
    public function complete(Request $request, Appointment $appointment)
    {
        if ($appointment->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $this->appointmentService->complete($appointment, $request->notes);

        return response()->json(['message' => 'Appointment completed successfully']);
    }

    /**
     * Get statistics.
     */
    public function statistics(Request $request)
    {
        $tenant = auth()->user()->tenant;
        $period = $request->query('period', 'month');

        $stats = $this->appointmentService->getStatistics($tenant->id, $period);

        return response()->json($stats);
    }

    /**
     * Export appointments.
     */
    public function export(Request $request)
    {
        $tenant = auth()->user()->tenant;
        
        $appointments = Appointment::where('tenant_id', $tenant->id)
            ->when($request->has('status'), function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->get();

        $csv = $this->generateCsv($appointments);

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, 'appointments-' . now()->format('Y-m-d') . '.csv');
    }

    /**
     * Generate CSV from appointments.
     */
    private function generateCsv($appointments)
    {
        $csv = "ID,Donor,Status,Scheduled,Completed\n";
        foreach ($appointments as $appointment) {
            $csv .= "{$appointment->id},{$appointment->donor->full_name},{$appointment->status},{$appointment->scheduled_at},{$appointment->completed_at}\n";
        }
        return $csv;
    }
}
