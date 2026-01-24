<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use App\Services\ApiKeyService;
use Illuminate\Http\Request;

class ApiKeyController extends Controller
{
    protected $apiKeyService;

    public function __construct(ApiKeyService $apiKeyService)
    {
        $this->apiKeyService = $apiKeyService;
    }

    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $apiKeys = $this->apiKeyService->getApiKeys($tenantId, auth()->id());

        return response()->json([
            'data' => $apiKeys->makeHidden(['key', 'secret']),
            'total' => $apiKeys->count(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'nullable|json',
            'scopes' => 'nullable|json',
            'expires_at' => 'nullable|date|after:now',
        ]);

        try {
            $apiKey = $this->apiKeyService->createApiKey(
                auth()->user()->tenant_id,
                auth()->id(),
                $validated['name'],
                $validated['permissions'] ? json_decode($validated['permissions'], true) : [],
                $validated['scopes'] ? json_decode($validated['scopes'], true) : [],
                $validated['expires_at'] ?? null
            );

            return response()->json([
                'message' => 'API key created successfully',
                'data' => $apiKey,
                'key' => substr($apiKey->key, 0, 10) . '...',
                'warning' => 'Store your API key securely. You won\'t be able to see it again.',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create API key',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function show(ApiKey $apiKey)
    {
        $this->authorize('view', $apiKey);

        return response()->json($apiKey->makeHidden(['key', 'secret']));
    }

    public function update(Request $request, ApiKey $apiKey)
    {
        $this->authorize('update', $apiKey);

        $validated = $request->validate([
            'permissions' => 'nullable|json',
            'scopes' => 'nullable|json',
            'rate_limit' => 'nullable|integer|min:100',
        ]);

        $apiKey = $this->apiKeyService->updateApiKey($apiKey->id, $validated);

        return response()->json([
            'message' => 'API key updated successfully',
            'data' => $apiKey->makeHidden(['key', 'secret']),
        ]);
    }

    public function revoke(ApiKey $apiKey)
    {
        $this->authorize('delete', $apiKey);

        $this->apiKeyService->revokeApiKey($apiKey->id);

        return response()->json([
            'message' => 'API key revoked successfully',
        ]);
    }

    public function regenerate(ApiKey $apiKey)
    {
        $this->authorize('update', $apiKey);

        try {
            $apiKey = $this->apiKeyService->regenerateApiKey($apiKey->id);

            return response()->json([
                'message' => 'API key regenerated successfully',
                'data' => $apiKey,
                'key' => substr($apiKey->key, 0, 10) . '...',
                'warning' => 'Old API key is no longer valid. Update your integrations.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to regenerate API key',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function grantPermission(Request $request, ApiKey $apiKey)
    {
        $this->authorize('update', $apiKey);

        $validated = $request->validate([
            'permission' => 'required|string',
        ]);

        $apiKey = $this->apiKeyService->grantPermission($apiKey->id, $validated['permission']);

        return response()->json([
            'message' => 'Permission granted',
            'data' => $apiKey,
        ]);
    }

    public function revokePermission(Request $request, ApiKey $apiKey)
    {
        $this->authorize('update', $apiKey);

        $validated = $request->validate([
            'permission' => 'required|string',
        ]);

        $apiKey = $this->apiKeyService->revokePermission($apiKey->id, $validated['permission']);

        return response()->json([
            'message' => 'Permission revoked',
            'data' => $apiKey,
        ]);
    }
}
