<?php

namespace Database\Seeders;

use App\Models\Webhook;
use App\Models\Integration;
use Illuminate\Database\Seeder;

class Phase3Seeder extends Seeder
{
    public function run()
    {
        // Seed webhook templates for each tenant
        $tenants = \App\Models\Tenant::all();

        foreach ($tenants as $tenant) {
            // Create example webhooks for common events
            $webhookEvents = [
                'donor.created',
                'donation.recorded',
                'inventory.low_stock',
                'appointment.scheduled',
            ];

            foreach ($webhookEvents as $event) {
                Webhook::create([
                    'tenant_id' => $tenant->id,
                    'event_type' => $event,
                    'url' => 'https://example.com/webhooks/' . str_replace('.', '-', $event),
                    'description' => "Example webhook for $event",
                    'retry_count' => 3,
                    'retry_delay' => 300,
                    'is_active' => false,
                ]);
            }

            // Initialize SMS integration (template)
            Integration::firstOrCreate(
                ['tenant_id' => $tenant->id, 'name' => 'twilio'],
                [
                    'type' => 'sms',
                    'credentials' => [
                        'account_sid' => '',
                        'auth_token' => '',
                        'from_number' => '',
                    ],
                    'config' => [
                        'retry_on_failure' => true,
                        'max_retries' => 3,
                    ],
                    'is_active' => false,
                ]
            );

            // Initialize email integration (template)
            Integration::firstOrCreate(
                ['tenant_id' => $tenant->id, 'name' => 'sendgrid'],
                [
                    'type' => 'email',
                    'credentials' => [
                        'api_key' => '',
                        'from_email' => '',
                    ],
                    'is_active' => false,
                ]
            );

            // Initialize EHR integration (template)
            Integration::firstOrCreate(
                ['tenant_id' => $tenant->id, 'name' => 'epic'],
                [
                    'type' => 'ehr',
                    'credentials' => [
                        'client_id' => '',
                        'client_secret' => '',
                        'fhir_endpoint' => '',
                    ],
                    'config' => [
                        'auto_sync' => false,
                        'sync_interval' => 3600,
                    ],
                    'is_active' => false,
                ]
            );

            // Initialize Stripe integration (template)
            Integration::firstOrCreate(
                ['tenant_id' => $tenant->id, 'name' => 'stripe'],
                [
                    'type' => 'payment',
                    'credentials' => [
                        'secret_key' => '',
                        'publishable_key' => '',
                    ],
                    'is_active' => false,
                ]
            );
        }
    }
}
