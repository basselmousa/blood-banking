<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\EligibilityTemplate;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use App\Models\CustomBranding;
use Illuminate\Database\Seeder;

class Phase4Seeder extends Seeder
{
    public function run()
    {
        // Get the first tenant (or create a test tenant)
        $tenant = Tenant::first() ?? Tenant::create(['name' => 'Test Tenant', 'slug' => 'test-tenant']);

        // Create default eligibility templates
        $this->createDefaultEligibilityTemplates($tenant);

        // Create sample workflows
        $this->createSampleWorkflows($tenant);

        // Create default branding
        $this->createDefaultBranding($tenant);

        $this->command->info('Phase 4 seeder completed successfully!');
    }

    private function createDefaultEligibilityTemplates($tenant)
    {
        $templates = [
            [
                'name' => 'Standard Blood Donor Requirements',
                'description' => 'Standard eligibility criteria for blood donors',
                'is_default' => true,
                'is_active' => true,
                'age_range' => json_encode(['min' => 18, 'max' => 65]),
                'weight_limit' => 50,
                'hemoglobin_levels' => json_encode(['min_male' => 13.5, 'min_female' => 12.5]),
                'blood_pressure' => json_encode(['systolic_max' => 180, 'diastolic_max' => 100]),
                'deferral_periods' => json_encode([
                    'cold_or_fever' => 14,
                    'surgery' => 30,
                    'vaccination' => 14,
                    'pregnancy' => 180,
                    'blood_transfusion' => 120,
                ]),
                'medications_to_defer' => json_encode(['Aspirin', 'Warfarin', 'Isotretinoin']),
                'conditions_to_defer' => json_encode(['HIV', 'Hepatitis', 'Syphilis', 'Malaria']),
            ],
            [
                'name' => 'Strict Medical Requirements',
                'description' => 'More stringent eligibility criteria',
                'is_default' => false,
                'is_active' => false,
                'age_range' => json_encode(['min' => 18, 'max' => 55]),
                'weight_limit' => 60,
                'hemoglobin_levels' => json_encode(['min_male' => 14.0, 'min_female' => 13.0]),
                'blood_pressure' => json_encode(['systolic_max' => 140, 'diastolic_max' => 90]),
                'deferral_periods' => json_encode([
                    'cold_or_fever' => 21,
                    'surgery' => 45,
                    'vaccination' => 21,
                    'pregnancy' => 365,
                    'blood_transfusion' => 180,
                ]),
                'medications_to_defer' => json_encode(['Aspirin', 'Warfarin', 'Isotretinoin', 'Antibiotics']),
                'conditions_to_defer' => json_encode(['HIV', 'Hepatitis', 'Syphilis', 'Malaria', 'Diabetes']),
            ],
        ];

        foreach ($templates as $template) {
            EligibilityTemplate::updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'name' => $template['name'],
                ],
                $template
            );
        }

        $this->command->info('Created eligibility templates');
    }

    private function createSampleWorkflows($tenant)
    {
        // Workflow 1: Send thank you email after donation
        $workflow1 = Workflow::create([
            'tenant_id' => $tenant->id,
            'name' => 'Send Thank You Email After Donation',
            'description' => 'Automatically send a thank you email to donors after successful donation',
            'trigger_type' => 'donation.completed',
            'trigger_conditions' => json_encode([]),
            'is_active' => true,
            'execution_order' => 1,
        ]);

        WorkflowStep::create([
            'tenant_id' => $tenant->id,
            'workflow_id' => $workflow1->id,
            'step_number' => 1,
            'action_type' => 'send_email',
            'action_config' => json_encode([
                'subject' => 'Thank You for Donating!',
                'body' => 'We appreciate your generous donation. It will help save lives.',
            ]),
            'is_conditional' => false,
            'wait_hours' => 0,
        ]);

        // Workflow 2: Defer donor after failed eligibility
        $workflow2 = Workflow::create([
            'tenant_id' => $tenant->id,
            'name' => 'Defer Donor After Failed Eligibility',
            'description' => 'Automatically defer a donor when they fail eligibility screening',
            'trigger_type' => 'eligibility.failed',
            'trigger_conditions' => json_encode([]),
            'is_active' => true,
            'execution_order' => 2,
        ]);

        WorkflowStep::create([
            'tenant_id' => $tenant->id,
            'workflow_id' => $workflow2->id,
            'step_number' => 1,
            'action_type' => 'defer_donor',
            'action_config' => json_encode([
                'deferral_type' => 'temporary',
                'reason' => 'Failed eligibility screening',
            ]),
            'is_conditional' => false,
            'wait_hours' => 0,
        ]);

        WorkflowStep::create([
            'tenant_id' => $tenant->id,
            'workflow_id' => $workflow2->id,
            'step_number' => 2,
            'action_type' => 'send_email',
            'action_config' => json_encode([
                'subject' => 'We need you to schedule a follow-up',
                'body' => 'We found some health concerns during your screening. Please visit us again.',
            ]),
            'is_conditional' => false,
            'wait_hours' => 0,
        ]);

        // Workflow 3: Send appointment reminder
        $workflow3 = Workflow::create([
            'tenant_id' => $tenant->id,
            'name' => 'Send Appointment Reminder SMS',
            'description' => 'Send SMS reminder 24 hours before appointment',
            'trigger_type' => 'appointment.scheduled',
            'trigger_conditions' => json_encode([]),
            'is_active' => false,
            'execution_order' => 3,
        ]);

        WorkflowStep::create([
            'tenant_id' => $tenant->id,
            'workflow_id' => $workflow3->id,
            'step_number' => 1,
            'action_type' => 'send_sms',
            'action_config' => json_encode([
                'message' => 'Reminder: You have a blood donation appointment tomorrow. Please arrive 15 minutes early.',
            ]),
            'is_conditional' => false,
            'wait_hours' => 24,
        ]);

        $this->command->info('Created sample workflows');
    }

    private function createDefaultBranding($tenant)
    {
        CustomBranding::updateOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'primary_color' => '#FF6B6B',
                'secondary_color' => '#4ECDC4',
                'accent_color' => '#45B7D1',
                'text_color' => '#2C3E50',
                'background_color' => '#FFFFFF',
                'font_family' => 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif',
                'company_name' => 'Blood Banking System',
                'company_description' => 'A comprehensive blood banking management system',
                'support_email' => 'support@bloodbank.local',
                'support_phone' => '+1-800-BLOOD-BANK',
                'hide_saas_branding' => false,
                'show_powered_by' => true,
            ]
        );

        $this->command->info('Created default branding');
    }
}
