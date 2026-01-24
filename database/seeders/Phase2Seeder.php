<?php

namespace Database\Seeders;

use App\Models\DonorPortalSettings;
use App\Models\Inventory;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class Phase2Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Initialize donor portal settings for all tenants
        Tenant::all()->each(function ($tenant) {
            DonorPortalSettings::updateOrCreate(
                ['tenant_id' => $tenant->id],
                [
                    'enabled' => true,
                    'allow_appointment_booking' => true,
                    'allow_eligibility_self_check' => true,
                    'show_inventory_status' => false,
                    'send_appointment_reminders' => true,
                    'send_donation_records' => true,
                    'appointment_reminder_hours' => 24,
                ]
            );

            // Initialize inventory for each blood type
            $bloodTypes = ['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'];
            $components = ['whole_blood', 'red_cells', 'plasma', 'platelets', 'cryoprecipitate'];

            foreach ($bloodTypes as $bloodType) {
                foreach ($components as $component) {
                    Inventory::updateOrCreate(
                        [
                            'tenant_id' => $tenant->id,
                            'blood_type' => $bloodType,
                            'component_type' => $component,
                        ],
                        [
                            'quantity' => 0,
                            'quantity_unit' => 1,
                            'critical_level' => 5,
                            'maximum_level' => 50,
                        ]
                    );
                }
            }
        });
    }
}
