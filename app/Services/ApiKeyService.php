<?php

namespace App\Services;

use App\Models\ApiKey;
use Illuminate\Support\Str;

class ApiKeyService
{
    public function createApiKey($tenantId, $userId, $name, $permissions = [], $scopes = [], $expiresAt = null)
    {
        $apiKey = ApiKey::create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'name' => $name,
            'permissions' => $permissions ?: ['read:all', 'write:all'],
            'scopes' => $scopes ?: ['donors', 'donations', 'appointments', 'inventory'],
            'rate_limit' => 1000,
            'expires_at' => $expiresAt,
            'is_active' => true,
        ]);

        return $apiKey;
    }

    public function updateApiKey($apiKeyId, $data)
    {
        $apiKey = ApiKey::findOrFail($apiKeyId);

        $apiKey->update(array_filter($data, function ($value) {
            return $value !== null;
        }));

        return $apiKey;
    }

    public function revokeApiKey($apiKeyId)
    {
        ApiKey::findOrFail($apiKeyId)->update(['is_active' => false]);
    }

    public function regenerateApiKey($apiKeyId)
    {
        $apiKey = ApiKey::findOrFail($apiKeyId);

        $apiKey->update([
            'key' => hash('sha256', Str::random(32)),
            'secret' => hash('sha256', Str::random(32)),
        ]);

        return $apiKey;
    }

    public function getApiKeys($tenantId, $userId = null)
    {
        $query = ApiKey::where('tenant_id', $tenantId);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->get();
    }

    public function validateApiKey($key)
    {
        $apiKey = ApiKey::where('key', $key)
            ->active()
            ->first();

        if (!$apiKey) {
            return null;
        }

        $apiKey->recordUsage();

        return $apiKey;
    }

    public function checkRateLimit($apiKeyId)
    {
        $apiKey = ApiKey::findOrFail($apiKeyId);

        $count = \Illuminate\Support\Facades\Cache::get("api_key_{$apiKeyId}", 0);

        if ($count >= $apiKey->rate_limit) {
            return false;
        }

        \Illuminate\Support\Facades\Cache::increment("api_key_{$apiKeyId}");
        \Illuminate\Support\Facades\Cache::expire("api_key_{$apiKeyId}", 3600); // 1 hour

        return true;
    }

    public function grantPermission($apiKeyId, $permission)
    {
        $apiKey = ApiKey::findOrFail($apiKeyId);

        $permissions = $apiKey->permissions ?? [];
        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $apiKey->update(['permissions' => $permissions]);
        }

        return $apiKey;
    }

    public function revokePermission($apiKeyId, $permission)
    {
        $apiKey = ApiKey::findOrFail($apiKeyId);

        $permissions = array_filter($apiKey->permissions ?? [], function ($p) use ($permission) {
            return $p !== $permission;
        });

        $apiKey->update(['permissions' => $permissions]);

        return $apiKey;
    }
}
