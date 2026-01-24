<?php

namespace Database\Seeders\SaaS;

use App\Models\MobileDevice;
use App\Models\OfflineSync;
use App\Models\PushNotification;
use App\Models\BiometricAuth;
use App\Models\QrCodeScan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class Phase5Seeder extends Seeder
{
    public function run()
    {
        $tenant = Tenant::first();

        if (!$tenant) {
            $this->command->warn('No tenant found. Please run Phase 1 seeder first.');
            return;
        }

        $users = User::where('tenant_id', $tenant->id)->limit(5)->get();

        if ($users->isEmpty()) {
            $this->command->warn('No users found in this tenant. Creating demo users.');
            $users = User::factory(5)->for($tenant)->create();
        }

        $this->seedMobileDevices($tenant, $users);
        $this->seedBiometricAuths($users);
        $this->seedPushNotifications($tenant, $users);
        $this->seedOfflineSyncs($users);
        $this->seedQrCodeScans($tenant, $users);

        $this->command->info('Phase 5 seeding completed successfully!');
    }

    private function seedMobileDevices(Tenant $tenant, $users)
    {
        $this->command->info('Seeding mobile devices...');

        $devices = [
            ['device_type' => 'ios', 'os_version' => '15.0', 'app_version' => '1.0.0'],
            ['device_type' => 'android', 'os_version' => '12.0', 'app_version' => '1.0.0'],
            ['device_type' => 'ios', 'os_version' => '16.0', 'app_version' => '1.0.0'],
            ['device_type' => 'android', 'os_version' => '13.0', 'app_version' => '1.0.0'],
        ];

        foreach ($users as $user) {
            foreach ($devices as $device) {
                MobileDevice::create([
                    'user_id' => $user->id,
                    'tenant_id' => $tenant->id,
                    'device_id' => 'DEVICE-' . uniqid(),
                    'device_type' => $device['device_type'],
                    'os_version' => $device['os_version'],
                    'app_version' => $device['app_version'],
                    'fcm_token' => 'fcm_' . uniqid(),
                    'apns_token' => 'apns_' . uniqid(),
                    'notifications_enabled' => true,
                    'biometric_enabled' => true,
                    'last_active_at' => now(),
                    'last_sync_at' => now(),
                ]);
            }
        }

        $this->command->line('  ✓ Created ' . ($users->count() * 4) . ' mobile devices');
    }

    private function seedBiometricAuths($users)
    {
        $this->command->info('Seeding biometric authentications...');

        $authTypes = ['fingerprint', 'face_recognition', 'iris'];
        $count = 0;

        foreach ($users as $user) {
            $devices = MobileDevice::where('user_id', $user->id)->limit(2)->get();

            foreach ($devices as $device) {
                foreach ($authTypes as $authType) {
                    BiometricAuth::create([
                        'user_id' => $user->id,
                        'mobile_device_id' => $device->id,
                        'auth_type' => $authType,
                        'encrypted_token' => encrypt('biometric_token_' . uniqid()),
                        'failed_attempts' => 0,
                        'locked_until' => null,
                        'is_enabled' => true,
                        'is_primary' => $authType === 'fingerprint',
                        'last_used_at' => now()->subHours(rand(1, 48)),
                    ]);
                    $count++;
                }
            }
        }

        $this->command->line('  ✓ Created ' . $count . ' biometric authentications');
    }

    private function seedPushNotifications(Tenant $tenant, $users)
    {
        $this->command->info('Seeding push notifications...');

        $notificationTypes = [
            'appointment_reminder',
            'eligibility_update',
            'donation_request',
            'system_alert',
            'custom',
        ];

        $titles = [
            'appointment_reminder' => 'Appointment Reminder',
            'eligibility_update' => 'Eligibility Status Updated',
            'donation_request' => 'Donation Request',
            'system_alert' => 'System Alert',
            'custom' => 'New Message',
        ];

        $bodies = [
            'appointment_reminder' => 'You have an upcoming appointment tomorrow at 2:00 PM.',
            'eligibility_update' => 'Your donation eligibility status has been updated.',
            'donation_request' => 'We need your help! Please consider making a donation.',
            'system_alert' => 'Important system maintenance scheduled for tonight.',
            'custom' => 'You have a new message from the admin.',
        ];

        $count = 0;

        foreach ($users as $user) {
            $devices = MobileDevice::where('user_id', $user->id)->get();

            foreach ($devices as $device) {
                foreach ($notificationTypes as $type) {
                    for ($i = 0; $i < rand(1, 3); $i++) {
                        PushNotification::create([
                            'user_id' => $user->id,
                            'mobile_device_id' => $device->id,
                            'tenant_id' => $tenant->id,
                            'title' => $titles[$type],
                            'body' => $bodies[$type],
                            'notification_type' => $type,
                            'action_url' => null,
                            'data' => ['key' => 'value'],
                            'status' => rand(0, 1) ? 'sent' : 'read',
                            'sent_at' => now()->subHours(rand(1, 72)),
                            'read_at' => rand(0, 1) ? now()->subHours(rand(1, 48)) : null,
                        ]);
                        $count++;
                    }
                }
            }
        }

        $this->command->line('  ✓ Created ' . $count . ' push notifications');
    }

    private function seedOfflineSyncs($users)
    {
        $this->command->info('Seeding offline syncs...');

        $actions = ['create', 'update', 'delete'];
        $entityTypes = ['donor', 'patient', 'appointment'];
        $count = 0;

        foreach ($users as $user) {
            $devices = MobileDevice::where('user_id', $user->id)->get();

            foreach ($devices as $device) {
                for ($i = 0; $i < rand(2, 5); $i++) {
                    OfflineSync::create([
                        'user_id' => $user->id,
                        'mobile_device_id' => $device->id,
                        'tenant_id' => $device->tenant_id,
                        'entity_type' => $entityTypes[array_rand($entityTypes)],
                        'entity_id' => rand(1, 100),
                        'action' => $actions[array_rand($actions)],
                        'data' => ['field' => 'value', 'timestamp' => now()],
                        'status' => rand(0, 2) ? 'synced' : (rand(0, 1) ? 'pending' : 'failed'),
                        'conflict_data' => null,
                        'error_message' => null,
                        'retry_count' => rand(0, 3),
                        'last_retry_at' => rand(0, 1) ? now()->subHours(rand(1, 24)) : null,
                    ]);
                    $count++;
                }
            }
        }

        $this->command->line('  ✓ Created ' . $count . ' offline syncs');
    }

    private function seedQrCodeScans(Tenant $tenant, $users)
    {
        $this->command->info('Seeding QR code scans...');

        $entityTypes = ['DONOR', 'PATIENT', 'APPOINTMENT', 'CAMP', 'INVENTORY'];
        $scanStatuses = ['success', 'failed', 'not_found', 'invalid'];
        $count = 0;

        foreach ($users as $user) {
            $devices = MobileDevice::where('user_id', $user->id)->get();

            foreach ($devices as $device) {
                for ($i = 0; $i < rand(3, 8); $i++) {
                    $entityType = $entityTypes[array_rand($entityTypes)];
                    $entityId = rand(1, 100);
                    $status = $scanStatuses[array_rand($scanStatuses)];

                    QrCodeScan::create([
                        'user_id' => $user->id,
                        'mobile_device_id' => $device->id,
                        'tenant_id' => $tenant->id,
                        'entity_type' => $entityType,
                        'entity_id' => $entityId,
                        'qr_code_value' => $entityType . ':' . $entityId,
                        'scan_status' => $status,
                        'scan_metadata' => [
                            'timestamp' => now()->subHours(rand(1, 72))->toIso8601String(),
                            'device_id' => $device->device_id,
                            'os' => $device->device_type,
                            'location' => [
                                'latitude' => 40.7128 + (rand(-10, 10) / 100),
                                'longitude' => -74.0060 + (rand(-10, 10) / 100),
                            ],
                        ],
                    ]);
                    $count++;
                }
            }
        }

        $this->command->line('  ✓ Created ' . $count . ' QR code scans');
    }
}
