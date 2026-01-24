<?php

namespace App\Http\Controllers;

use App\Models\Webhook;
use App\Services\WebhookService;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    protected $webhookService;

    public function __construct(WebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
    }

    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $eventType = $request->query('event_type');

        $webhooks = $this->webhookService->getWebhooks($tenantId, $eventType);

        return response()->json([
            'data' => $webhooks,
            'total' => $webhooks->count(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_type' => 'required|string',
            'url' => 'required|url',
            'description' => 'nullable|string',
            'secret' => 'nullable|string',
            'filters' => 'nullable|json',
            'headers' => 'nullable|json',
            'retry_count' => 'nullable|integer|min:1|max:10',
            'retry_delay' => 'nullable|integer|min:60',
        ]);

        $tenantId = auth()->user()->tenant_id;

        try {
            $webhook = $this->webhookService->registerWebhook($tenantId, $validated);

            return response()->json([
                'message' => 'Webhook registered successfully',
                'data' => $webhook,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to register webhook',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function show(Webhook $webhook)
    {
        $this->authorize('view', $webhook);

        return response()->json($webhook);
    }

    public function update(Request $request, Webhook $webhook)
    {
        $this->authorize('update', $webhook);

        $validated = $request->validate([
            'url' => 'nullable|url',
            'description' => 'nullable|string',
            'filters' => 'nullable|json',
            'headers' => 'nullable|json',
            'is_active' => 'nullable|boolean',
            'retry_count' => 'nullable|integer|min:1|max:10',
        ]);

        $webhook = $this->webhookService->updateWebhook($webhook->id, $validated);

        return response()->json([
            'message' => 'Webhook updated successfully',
            'data' => $webhook,
        ]);
    }

    public function delete(Webhook $webhook)
    {
        $this->authorize('delete', $webhook);

        $this->webhookService->deleteWebhook($webhook->id);

        return response()->json([
            'message' => 'Webhook deleted successfully',
        ]);
    }

    public function deliveryHistory(Webhook $webhook)
    {
        $this->authorize('view', $webhook);

        $deliveries = $this->webhookService->getDeliveryHistory($webhook->id);

        return response()->json([
            'data' => $deliveries,
            'total' => $deliveries->count(),
        ]);
    }

    public function test(Webhook $webhook)
    {
        $this->authorize('update', $webhook);

        try {
            $delivery = $this->webhookService->testWebhook($webhook->id);

            return response()->json([
                'message' => 'Test webhook sent',
                'data' => $delivery,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send test webhook',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function retryFailed(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $count = $this->webhookService->retryFailedDeliveries($tenantId);

        return response()->json([
            'message' => "Retrying $count failed deliveries",
            'count' => $count,
        ]);
    }
}
