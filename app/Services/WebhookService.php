<?php

namespace App\Services;

use App\Models\Webhook;
use App\Models\WebhookDelivery;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WebhookService
{
    protected $allowedEventTypes = [
        'donor.created',
        'donor.updated',
        'donor.eligible',
        'donor.deferred',
        'donation.recorded',
        'donation.rejected',
        'inventory.low_stock',
        'inventory.expiring_soon',
        'appointment.scheduled',
        'appointment.completed',
        'appointment.cancelled',
        'subscription.created',
        'subscription.upgraded',
        'subscription.cancelled',
    ];

    public function registerWebhook($tenantId, $data)
    {
        if (!in_array($data['event_type'], $this->allowedEventTypes)) {
            throw new \Exception("Invalid event type: {$data['event_type']}");
        }

        $webhook = Webhook::create([
            'tenant_id' => $tenantId,
            'event_type' => $data['event_type'],
            'url' => $data['url'],
            'description' => $data['description'] ?? null,
            'secret' => $data['secret'] ?? Str::random(32),
            'filters' => $data['filters'] ?? null,
            'headers' => $data['headers'] ?? null,
            'retry_count' => $data['retry_count'] ?? 3,
            'retry_delay' => $data['retry_delay'] ?? 300,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return $webhook;
    }

    public function updateWebhook($webhookId, $data)
    {
        $webhook = Webhook::findOrFail($webhookId);

        $webhook->update(array_filter($data, function ($value) {
            return $value !== null;
        }));

        return $webhook;
    }

    public function deleteWebhook($webhookId)
    {
        Webhook::findOrFail($webhookId)->delete();
    }

    public function getWebhooks($tenantId, $eventType = null)
    {
        $query = Webhook::where('tenant_id', $tenantId)->active();

        if ($eventType) {
            $query->where('event_type', $eventType);
        }

        return $query->get();
    }

    public function trigger($tenantId, $eventType, $payload)
    {
        $webhooks = $this->getWebhooks($tenantId, $eventType);

        foreach ($webhooks as $webhook) {
            if (!$webhook->canDeliver($payload)) {
                continue;
            }

            $delivery = WebhookDelivery::create([
                'webhook_id' => $webhook->id,
                'event_type' => $eventType,
                'payload' => $payload,
                'status' => 'pending',
            ]);

            // Dispatch delivery job (async)
            \App\Jobs\DeliverWebhook::dispatch($delivery)->delay(now()->addSeconds(5));
        }
    }

    public function deliverWebhook(WebhookDelivery $delivery)
    {
        $webhook = $delivery->webhook;
        $payload = json_encode($delivery->payload);

        $headers = [
            'Content-Type' => 'application/json',
            'User-Agent' => 'BloodBankSaaS/1.0',
            'X-Webhook-ID' => $webhook->id,
            'X-Event-Type' => $delivery->event_type,
            'X-Timestamp' => now()->timestamp,
        ];

        if ($webhook->secret) {
            $headers['X-Signature'] = 'sha256=' . $webhook->getSignature($payload);
        }

        if ($webhook->headers) {
            $headers = array_merge($headers, $webhook->headers);
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders($headers)
                ->post($webhook->url, $delivery->payload);

            if ($response->successful()) {
                $delivery->markDelivered($response->status(), $response->body());
            } else {
                $delivery->markFailed($response->status(), $response->body());
            }
        } catch (\Exception $e) {
            $delivery->markFailed(0, $e->getMessage());
        }

        return $delivery;
    }

    public function retryFailedDeliveries($tenantId)
    {
        $pendingDeliveries = WebhookDelivery::whereHas('webhook', function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        })
        ->pending()
        ->get();

        foreach ($pendingDeliveries as $delivery) {
            \App\Jobs\DeliverWebhook::dispatch($delivery);
        }

        return $pendingDeliveries->count();
    }

    public function getDeliveryHistory($webhookId, $limit = 50)
    {
        return WebhookDelivery::where('webhook_id', $webhookId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function testWebhook($webhookId)
    {
        $webhook = Webhook::findOrFail($webhookId);

        $testPayload = [
            'event' => 'test',
            'timestamp' => now()->timestamp,
            'data' => [
                'message' => 'This is a test webhook',
            ],
        ];

        $delivery = WebhookDelivery::create([
            'webhook_id' => $webhook->id,
            'event_type' => 'test',
            'payload' => $testPayload,
            'status' => 'pending',
        ]);

        return $this->deliverWebhook($delivery);
    }
}
