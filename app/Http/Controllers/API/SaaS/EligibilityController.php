<?php

namespace App\Http\Controllers\API\SaaS;

use App\Http\Controllers\Controller;
use App\Models\EligibilityTemplate;
use App\Services\EligibilityService;
use Illuminate\Http\Request;

class EligibilityController extends Controller
{
    protected $eligibilityService;

    public function __construct(EligibilityService $eligibilityService)
    {
        $this->eligibilityService = $eligibilityService;
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        $tenantId = auth()->user()->current_tenant_id;

        $templates = $this->eligibilityService->listEligibilityTemplates($tenantId);

        return response()->json([
            'success' => true,
            'data' => $templates,
            'count' => $templates->count(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'age_range' => 'nullable|json',
            'hemoglobin_levels' => 'nullable|json',
            'weight_limit' => 'nullable|numeric',
            'blood_pressure' => 'nullable|json',
            'custom_questions' => 'nullable|json',
            'deferral_periods' => 'nullable|json',
            'medications_to_defer' => 'nullable|json',
            'conditions_to_defer' => 'nullable|json',
        ]);

        $tenantId = auth()->user()->current_tenant_id;

        $template = $this->eligibilityService->createEligibilityTemplate($tenantId, $request->all());

        return response()->json([
            'success' => true,
            'data' => $template,
            'message' => 'Eligibility template created successfully',
        ], 201);
    }

    public function show(EligibilityTemplate $eligibilityTemplate)
    {
        $this->authorize('view', $eligibilityTemplate);

        return response()->json([
            'success' => true,
            'data' => $eligibilityTemplate,
        ]);
    }

    public function update(Request $request, EligibilityTemplate $eligibilityTemplate)
    {
        $this->authorize('update', $eligibilityTemplate);

        $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'age_range' => 'nullable|json',
            'hemoglobin_levels' => 'nullable|json',
            'weight_limit' => 'nullable|numeric',
            'blood_pressure' => 'nullable|json',
            'custom_questions' => 'nullable|json',
            'deferral_periods' => 'nullable|json',
            'medications_to_defer' => 'nullable|json',
            'conditions_to_defer' => 'nullable|json',
        ]);

        $template = $this->eligibilityService->updateEligibilityTemplate(
            $eligibilityTemplate->id,
            $request->all()
        );

        return response()->json([
            'success' => true,
            'data' => $template,
            'message' => 'Eligibility template updated successfully',
        ]);
    }

    public function createVersion(EligibilityTemplate $eligibilityTemplate)
    {
        $this->authorize('update', $eligibilityTemplate);

        $newVersion = $this->eligibilityService->createTemplateVersion($eligibilityTemplate->id);

        return response()->json([
            'success' => true,
            'data' => $newVersion,
            'message' => 'New template version created successfully',
        ], 201);
    }

    public function setAsDefault(EligibilityTemplate $eligibilityTemplate)
    {
        $this->authorize('update', $eligibilityTemplate);

        $template = $this->eligibilityService->setAsDefault($eligibilityTemplate->id);

        return response()->json([
            'success' => true,
            'data' => $template,
            'message' => 'Eligibility template set as default successfully',
        ]);
    }

    public function setAsActive(EligibilityTemplate $eligibilityTemplate)
    {
        $this->authorize('update', $eligibilityTemplate);

        $template = $this->eligibilityService->setAsActive($eligibilityTemplate->id);

        return response()->json([
            'success' => true,
            'data' => $template,
            'message' => 'Eligibility template activated successfully',
        ]);
    }

    public function destroy(EligibilityTemplate $eligibilityTemplate)
    {
        $this->authorize('delete', $eligibilityTemplate);

        $this->eligibilityService->deleteTemplate($eligibilityTemplate->id);

        return response()->json([
            'success' => true,
            'message' => 'Eligibility template deleted successfully',
        ]);
    }

    public function getActive()
    {
        $tenantId = auth()->user()->current_tenant_id;

        $template = $this->eligibilityService->getActiveTemplate($tenantId);

        if (!$template) {
            return response()->json([
                'success' => false,
                'message' => 'No active eligibility template',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $template,
        ]);
    }

    public function evaluateDonor(Request $request)
    {
        $request->validate([
            'donor_id' => 'required|exists:donors,id',
            'template_id' => 'nullable|exists:eligibility_templates,id',
        ]);

        // Get donor and verify tenant access
        $donor = \App\Models\Donor::findOrFail($request->donor_id);
        $this->authorize('view', $donor);

        $evaluation = $this->eligibilityService->evaluateDonorEligibility(
            $donor,
            $request->template_id
        );

        return response()->json([
            'success' => true,
            'data' => $evaluation,
        ]);
    }

    public function testEvaluation(Request $request)
    {
        $request->validate([
            'template_id' => 'required|exists:eligibility_templates,id',
            'donor_data' => 'required|array',
        ]);

        $template = EligibilityTemplate::findOrFail($request->template_id);
        $this->authorize('view', $template);

        // Create a temporary donor object for testing
        $testDonor = new \App\Models\Donor($request->donor_data);
        $evaluation = $template->evaluateDonor($testDonor);

        return response()->json([
            'success' => true,
            'data' => $evaluation,
            'message' => 'Evaluation test completed',
        ]);
    }

    public function getDefaultDeferralPeriods(EligibilityTemplate $eligibilityTemplate)
    {
        $this->authorize('view', $eligibilityTemplate);

        $deferralPeriods = $eligibilityTemplate->deferral_periods ?? [];

        return response()->json([
            'success' => true,
            'data' => $deferralPeriods,
        ]);
    }

    public function createDefaults()
    {
        // Only admins can create default templates
        if (!auth()->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $tenantId = auth()->user()->current_tenant_id;

        $templates = $this->eligibilityService->createDefaultTemplates($tenantId);

        return response()->json([
            'success' => true,
            'data' => $templates,
            'message' => 'Default eligibility templates created successfully',
        ], 201);
    }
}
