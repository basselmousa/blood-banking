<?php

namespace App\Services;

use App\Models\SmsMessage;
use Illuminate\Support\Facades\Http;

class SmsService
{
    protected $integration;

    public function __construct()
    {
        // Initialize with default SMS provider from config
    }

    public function sendAppointmentReminder($appointment)
    {
        $donor = $appointment->donor;

        $message = "Hello {$donor->first_name}, you have a donation appointment on {$appointment->scheduled_at->format('M d, Y \a\t H:i')}. Please arrive 10 minutes early.";

        return $this->sendSms(
            $donor->phone,
            $message,
            'appointment_reminder',
            $appointment
        );
    }

    public function sendEligibilityNotification($donor, $eligible)
    {
        $message = $eligible 
            ? "Hello {$donor->first_name}, you are eligible to donate! Schedule an appointment at [link]."
            : "Hello {$donor->first_name}, you are currently deferred from donating. Please check back in [days] days.";

        return $this->sendSms(
            $donor->phone,
            $message,
            'eligibility_notification',
            $donor
        );
    }

    public function sendDonationThankYou($donation)
    {
        $donor = $donation->donor;

        $message = "Thank you {$donor->first_name} for your donation! Your contribution helps save lives. Visit [link] for your donation record.";

        return $this->sendSms(
            $donor->phone,
            $message,
            'donation_thank_you',
            $donation
        );
    }

    public function sendInventoryAlert($tenantId, $alert)
    {
        // Send to admin/coordinator
        $message = "ALERT: {$alert['type']} - {$alert['message']}. Action required.";

        return $this->sendSms(
            $alert['phone'],
            $message,
            'inventory_alert',
            null,
            $tenantId
        );
    }

    public function sendSms($phoneNumber, $message, $type = 'general', $messageable = null, $tenantId = null)
    {
        $smsMessage = SmsMessage::create([
            'tenant_id' => $tenantId,
            'messageable_type' => $messageable ? get_class($messageable) : null,
            'messageable_id' => $messageable ? $messageable->id : null,
            'phone_number' => $phoneNumber,
            'message' => $message,
            'type' => $type,
            'status' => 'pending',
        ]);

        // Dispatch async job to send SMS
        \App\Jobs\SendSmsMessage::dispatch($smsMessage);

        return $smsMessage;
    }

    public function deliverSms(SmsMessage $smsMessage)
    {
        try {
            $provider = $smsMessage->provider ?? 'twilio';

            switch ($provider) {
                case 'twilio':
                    $result = $this->sendViaTwilio($smsMessage);
                    break;
                case 'aws_sns':
                    $result = $this->sendViaAwsSns($smsMessage);
                    break;
                default:
                    throw new \Exception("Unknown SMS provider: $provider");
            }

            $smsMessage->markSent($result['message_id'] ?? null);

            return $result;
        } catch (\Exception $e) {
            $smsMessage->markFailed($e->getMessage());

            if ($smsMessage->canRetry()) {
                \App\Jobs\SendSmsMessage::dispatch($smsMessage)->delay(now()->addMinutes(5));
            }

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function sendViaTwilio(SmsMessage $smsMessage)
    {
        $integration = app(\App\Services\IntegrationService::class)
            ->getIntegration($smsMessage->tenant_id, 'twilio');

        if (!$integration) {
            throw new \Exception('Twilio integration not configured');
        }

        $response = Http::asForm()
            ->withBasicAuth(
                $integration->getCredential('account_sid'),
                $integration->getCredential('auth_token')
            )
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$integration->getCredential('account_sid')}/Messages.json", [
                'From' => $integration->getCredential('from_number'),
                'To' => $smsMessage->phone_number,
                'Body' => $smsMessage->message,
            ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to send SMS via Twilio: ' . $response->body());
        }

        return [
            'success' => true,
            'message_id' => $response->json('sid'),
            'provider' => 'twilio',
        ];
    }

    private function sendViaAwsSns(SmsMessage $smsMessage)
    {
        $integration = app(\App\Services\IntegrationService::class)
            ->getIntegration($smsMessage->tenant_id, 'aws_sns');

        if (!$integration) {
            throw new \Exception('AWS SNS integration not configured');
        }

        // AWS SNS SDK implementation
        $sns = new \Aws\Sns\SnsClient([
            'version' => 'latest',
            'region' => 'us-east-1',
            'credentials' => [
                'key' => $integration->getCredential('access_key'),
                'secret' => $integration->getCredential('secret_key'),
            ],
        ]);

        $result = $sns->publish([
            'Message' => $smsMessage->message,
            'PhoneNumber' => $smsMessage->phone_number,
        ]);

        return [
            'success' => true,
            'message_id' => $result['MessageId'],
            'provider' => 'aws_sns',
        ];
    }

    public function getSmsHistory($tenantId, $limit = 50)
    {
        return SmsMessage::where('tenant_id', $tenantId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getSmsStatistics($tenantId, $period = 'month')
    {
        $query = SmsMessage::where('tenant_id', $tenantId);

        if ($period === 'month') {
            $query->whereMonth('created_at', now()->month);
        } elseif ($period === 'week') {
            $query->whereDate('created_at', '>=', now()->subWeek());
        }

        return [
            'total' => $query->count(),
            'sent' => $query->clone()->where('status', 'sent')->count(),
            'delivered' => $query->clone()->where('status', 'delivered')->count(),
            'failed' => $query->clone()->where('status', 'failed')->count(),
            'by_type' => $query->clone()->groupBy('type')->selectRaw('type, count(*) as count')->get(),
        ];
    }
}
