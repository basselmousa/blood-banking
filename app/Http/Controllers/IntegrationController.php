<?php

namespace App\Http\Controllers;

use App\Models\Integration;
use App\Services\IntegrationService;
use Illuminate\Http\Request;

class IntegrationController extends Controller
{
    protected $integrationService;

    public function __construct(IntegrationService $integrationService)
    {
        $this->integrationService = $integrationService;
    }

    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $type = $request->query('type');

        $query = Integration::where('tenant_id', $tenantId);

        if ($type) {
            $query->where('type', $type);
        }

        $integrations = $query->get();

        return response()->json([
            'data' => $integrations->makeHidden(['credentials']),
            'total' => $integrations->count(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'type' => 'required|in:sms,email,payment,ehr,crm,accounting',
            'credentials' => 'required|json',
            'config' => 'nullable|json',
        ]);

        $tenantId = auth()->user()->tenant_id;

        try {
            $integration = $this->integrationService->createIntegration(
                $tenantId,
                $validated['name'],
                $validated['type'],
                json_decode($validated['credentials'], true),
                json_decode($validated['config'] ?? '{}', true)
            );

            return response()->json([
                'message' => 'Integration created successfully',
                'data' => $integration->makeHidden(['credentials']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create integration',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function show(Integration $integration)
    {
        $this->authorize('view', $integration);

        return response()->json($integration->makeHidden(['credentials']));
    }

    public function update(Request $request, Integration $integration)
    {
        $this->authorize('update', $integration);

        $validated = $request->validate([
            'credentials' => 'nullable|json',
            'config' => 'nullable|json',
        ]);

        try {
            $integration = $this->integrationService->updateIntegration(
                $integration->id,
                $validated['credentials'] ? json_decode($validated['credentials'], true) : null,
                $validated['config'] ? json_decode($validated['config'], true) : null
            );

            return response()->json([
                'message' => 'Integration updated successfully',
                'data' => $integration->makeHidden(['credentials']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update integration',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function delete(Integration $integration)
    {
        $this->authorize('delete', $integration);

        $integration->delete();

        return response()->json([
            'message' => 'Integration deleted successfully',
        ]);
    }

    public function test(Integration $integration)
    {
        $this->authorize('update', $integration);

        $result = $this->integrationService->testIntegration($integration->id);

        return response()->json($result);
    }

    public function toggle(Integration $integration)
    {
        $this->authorize('update', $integration);

        if ($integration->is_active) {
            $this->integrationService->disableIntegration($integration->id);
            $message = 'Integration disabled';
        } else {
            $this->integrationService->enableIntegration($integration->id);
            $message = 'Integration enabled';
        }

        return response()->json([
            'message' => $message,
            'data' => $integration->refresh(),
        ]);
    }

    public function sync(Integration $integration)
    {
        $this->authorize('update', $integration);

        try {
            $integration = $this->integrationService->syncIntegration($integration->id);

            return response()->json([
                'message' => 'Sync initiated',
                'data' => $integration,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Sync failed',
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
