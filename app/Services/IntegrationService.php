<?php

namespace App\Services;

use App\Models\Integration;
use Illuminate\Support\Facades\Http;

class IntegrationService
{
    public function createIntegration($tenantId, $name, $type, $credentials, $config = null)
    {
        $integration = Integration::create([
            'tenant_id' => $tenantId,
            'name' => $name,
            'type' => $type,
            'credentials' => $credentials,
            'config' => $config,
            'is_active' => true,
        ]);

        return $integration;
    }

    public function updateIntegration($integrationId, $credentials = null, $config = null)
    {
        $integration = Integration::findOrFail($integrationId);

        if ($credentials) {
            $integration->credentials = array_merge($integration->credentials ?? [], $credentials);
        }

        if ($config) {
            $integration->config = array_merge($integration->config ?? [], $config);
        }

        $integration->save();

        return $integration;
    }

    public function disableIntegration($integrationId)
    {
        Integration::findOrFail($integrationId)->update(['is_active' => false]);
    }

    public function enableIntegration($integrationId)
    {
        Integration::findOrFail($integrationId)->update(['is_active' => true]);
    }

    public function getIntegration($tenantId, $name)
    {
        return Integration::where('tenant_id', $tenantId)
            ->where('name', $name)
            ->first();
    }

    public function getIntegrationsByType($tenantId, $type)
    {
        return Integration::where('tenant_id', $tenantId)
            ->where('type', $type)
            ->active()
            ->get();
    }

    public function testIntegration($integrationId)
    {
        $integration = Integration::findOrFail($integrationId);

        try {
            switch ($integration->name) {
                case 'twilio':
                    return $this->testTwilio($integration);
                case 'sendgrid':
                    return $this->testSendGrid($integration);
                case 'stripe':
                    return $this->testStripe($integration);
                default:
                    return ['status' => 'unknown', 'message' => 'Unknown integration'];
            }
        } catch (\Exception $e) {
            return ['status' => 'failed', 'message' => $e->getMessage()];
        }
    }

    private function testTwilio($integration)
    {
        $response = Http::withBasicAuth(
            $integration->getCredential('account_sid'),
            $integration->getCredential('auth_token')
        )->get("https://api.twilio.com/2010-04-01/Accounts/{$integration->getCredential('account_sid')}.json");

        return [
            'status' => $response->successful() ? 'success' : 'failed',
            'message' => $response->successful() ? 'Twilio integration verified' : 'Invalid credentials',
        ];
    }

    private function testSendGrid($integration)
    {
        $response = Http::withToken($integration->getCredential('api_key'))
            ->get('https://api.sendgrid.com/v3/user/account');

        return [
            'status' => $response->successful() ? 'success' : 'failed',
            'message' => $response->successful() ? 'SendGrid integration verified' : 'Invalid credentials',
        ];
    }

    private function testStripe($integration)
    {
        $response = Http::withBasicAuth($integration->getCredential('secret_key'), '')
            ->get('https://api.stripe.com/v1/account');

        return [
            'status' => $response->successful() ? 'success' : 'failed',
            'message' => $response->successful() ? 'Stripe integration verified' : 'Invalid credentials',
        ];
    }

    public function syncIntegration($integrationId)
    {
        $integration = Integration::findOrFail($integrationId);

        $integration->setSyncStatus('syncing');

        try {
            // Implement provider-specific sync logic
            switch ($integration->name) {
                case 'salesforce':
                    $this->syncSalesforce($integration);
                    break;
                case 'quickbooks':
                    $this->syncQuickBooks($integration);
                    break;
            }

            $integration->setSyncStatus('idle');
        } catch (\Exception $e) {
            $integration->setSyncStatus('failed', $e->getMessage());
        }

        return $integration;
    }

    private function syncSalesforce($integration)
    {
        // Salesforce sync implementation
    }

    private function syncQuickBooks($integration)
    {
        // QuickBooks sync implementation
    }
}
