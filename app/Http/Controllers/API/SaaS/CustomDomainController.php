<?php

namespace App\Http\Controllers\API\SaaS;

use App\Http\Controllers\Controller;
use App\Models\CustomDomain;
use App\Services\DomainService;
use Illuminate\Http\Request;

class CustomDomainController extends Controller
{
    protected $domainService;

    public function __construct(DomainService $domainService)
    {
        $this->domainService = $domainService;
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        $tenantId = auth()->user()->current_tenant_id;

        $domains = $this->domainService->listDomainsForTenant($tenantId);

        return response()->json([
            'success' => true,
            'data' => $domains,
            'message' => 'Domains retrieved successfully',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'domain' => ['required', 'string', 'max:255'],
        ]);

        $tenantId = auth()->user()->current_tenant_id;

        // Validate domain format
        $validation = $this->domainService->validateDomainFormat($request->domain);
        if (!$validation['valid']) {
            return response()->json([
                'success' => false,
                'message' => $validation['error'],
            ], 422);
        }

        // Check availability
        if (!$this->domainService->isDomainAvailable($request->domain)) {
            return response()->json([
                'success' => false,
                'message' => 'Domain is already registered',
            ], 422);
        }

        $customDomain = $this->domainService->createDomain($tenantId, $request->domain);

        return response()->json([
            'success' => true,
            'data' => $customDomain,
            'message' => 'Domain created successfully. Please verify DNS records.',
        ], 201);
    }

    public function show(CustomDomain $customDomain)
    {
        $this->authorize('view', $customDomain);

        return response()->json([
            'success' => true,
            'data' => $customDomain,
        ]);
    }

    public function verify(Request $request, CustomDomain $customDomain)
    {
        $this->authorize('update', $customDomain);

        $request->validate([
            'token' => 'required|string',
        ]);

        try {
            $verified = $this->domainService->verifyDomain(
                $customDomain->id,
                $request->token
            );

            return response()->json([
                'success' => true,
                'data' => $verified,
                'message' => 'Domain verified successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Domain verification failed: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function getVerificationRecords(CustomDomain $customDomain)
    {
        $this->authorize('view', $customDomain);

        $records = $this->domainService->getVerificationDnsRecords($customDomain->id);

        return response()->json([
            'success' => true,
            'data' => $records,
        ]);
    }

    public function generateSsl(CustomDomain $customDomain)
    {
        $this->authorize('update', $customDomain);

        if (!$customDomain->is_verified) {
            return response()->json([
                'success' => false,
                'message' => 'Domain must be verified before SSL certificate generation',
            ], 422);
        }

        try {
            $domain = $this->domainService->generateSslCertificate($customDomain->id);

            return response()->json([
                'success' => true,
                'data' => $domain,
                'message' => 'SSL certificate generated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'SSL generation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function checkSslExpiration(CustomDomain $customDomain)
    {
        $this->authorize('view', $customDomain);

        $status = $this->domainService->checkSslExpiration($customDomain->id);

        return response()->json([
            'success' => true,
            'data' => $status,
        ]);
    }

    public function renewSsl(CustomDomain $customDomain)
    {
        $this->authorize('update', $customDomain);

        try {
            $domain = $this->domainService->renewSslCertificate($customDomain->id);

            return response()->json([
                'success' => true,
                'data' => $domain,
                'message' => 'SSL certificate renewed successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'SSL renewal failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function activate(CustomDomain $customDomain)
    {
        $this->authorize('update', $customDomain);

        try {
            $domain = $this->domainService->activateDomain($customDomain->id);

            return response()->json([
                'success' => true,
                'data' => $domain,
                'message' => 'Domain activated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Activation failed: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function deactivate(CustomDomain $customDomain)
    {
        $this->authorize('update', $customDomain);

        $domain = $this->domainService->deactivateDomain($customDomain->id);

        return response()->json([
            'success' => true,
            'data' => $domain,
            'message' => 'Domain deactivated successfully',
        ]);
    }

    public function setPrimary(CustomDomain $customDomain)
    {
        $this->authorize('update', $customDomain);

        $domain = $this->domainService->setPrimaryDomain($customDomain->id);

        return response()->json([
            'success' => true,
            'data' => $domain,
            'message' => 'Domain set as primary successfully',
        ]);
    }

    public function destroy(CustomDomain $customDomain)
    {
        $this->authorize('delete', $customDomain);

        $this->domainService->deleteDomain($customDomain->id);

        return response()->json([
            'success' => true,
            'message' => 'Domain deleted successfully',
        ]);
    }
}
