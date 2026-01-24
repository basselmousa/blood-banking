<?php

namespace App\Http\Controllers;

use App\Models\DonorPortalSettings;
use Illuminate\Http\Request;

class DonorPortalController extends Controller
{
    /**
     * Get donor portal settings.
     */
    public function settings(Request $request)
    {
        $tenant = auth()->user()->tenant;
        $settings = DonorPortalSettings::where('tenant_id', $tenant->id)->first();

        if (!$settings) {
            $settings = DonorPortalSettings::create(['tenant_id' => $tenant->id]);
        }

        return response()->json($settings);
    }

    /**
     * Update donor portal settings.
     */
    public function updateSettings(Request $request)
    {
        $tenant = auth()->user()->tenant;

        $validated = $request->validate([
            'enabled' => 'boolean',
            'allow_appointment_booking' => 'boolean',
            'allow_eligibility_self_check' => 'boolean',
            'show_inventory_status' => 'boolean',
            'send_appointment_reminders' => 'boolean',
            'send_donation_records' => 'boolean',
            'appointment_reminder_hours' => 'integer|min:1',
        ]);

        $settings = DonorPortalSettings::where('tenant_id', $tenant->id)->first();

        if (!$settings) {
            $settings = DonorPortalSettings::create(['tenant_id' => $tenant->id]);
        }

        $settings->update($validated);

        return response()->json(['message' => 'Settings updated successfully']);
    }

    /**
     * Get donor portal view (public).
     */
    public function view(Request $request)
    {
        $settings = DonorPortalSettings::where('tenant_id', auth()->user()->tenant_id)
            ->where('enabled', true)
            ->firstOrFail();

        $donor = auth()->user();
        $upcomingAppointments = $donor->appointments()->upcoming()->get();
        $pastAppointments = $donor->appointments()->past()->limit(5)->get();

        return view('donor-portal.index', compact('settings', 'donor', 'upcomingAppointments', 'pastAppointments'));
    }

    /**
     * Enable a feature.
     */
    public function enableFeature(Request $request)
    {
        $tenant = auth()->user()->tenant;

        $validated = $request->validate([
            'feature' => 'required|string',
        ]);

        $settings = DonorPortalSettings::where('tenant_id', $tenant->id)->first();

        if (!$settings) {
            $settings = DonorPortalSettings::create(['tenant_id' => $tenant->id]);
        }

        $settings->enableFeature($validated['feature']);

        return response()->json(['message' => 'Feature enabled']);
    }

    /**
     * Disable a feature.
     */
    public function disableFeature(Request $request)
    {
        $tenant = auth()->user()->tenant;

        $validated = $request->validate([
            'feature' => 'required|string',
        ]);

        $settings = DonorPortalSettings::where('tenant_id', $tenant->id)->first();

        if (!$settings) {
            $settings = DonorPortalSettings::create(['tenant_id' => $tenant->id]);
        }

        $settings->disableFeature($validated['feature']);

        return response()->json(['message' => 'Feature disabled']);
    }
}
