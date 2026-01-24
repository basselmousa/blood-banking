<?php

namespace App\Http\Controllers\API\SaaS;

use App\Http\Controllers\Controller;
use App\Models\Workflow;
use App\Models\WorkflowExecution;
use App\Services\WorkflowService;
use Illuminate\Http\Request;

class WorkflowController extends Controller
{
    protected $workflowService;

    public function __construct(WorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        $tenantId = auth()->user()->current_tenant_id;

        $workflows = $this->workflowService->listWorkflows($tenantId);

        return response()->json([
            'success' => true,
            'data' => $workflows,
            'count' => $workflows->count(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'trigger_type' => 'required|string|in:' . implode(',', Workflow::TRIGGER_TYPES),
            'trigger_conditions' => 'nullable|json',
            'is_active' => 'nullable|boolean',
            'execution_order' => 'nullable|integer',
        ]);

        $tenantId = auth()->user()->current_tenant_id;

        $workflow = $this->workflowService->createWorkflow($tenantId, $request->all());

        return response()->json([
            'success' => true,
            'data' => $workflow,
            'message' => 'Workflow created successfully',
        ], 201);
    }

    public function show(Workflow $workflow)
    {
        $this->authorize('view', $workflow);

        $workflowWithSteps = $workflow->load('steps');

        return response()->json([
            'success' => true,
            'data' => $workflowWithSteps,
        ]);
    }

    public function update(Request $request, Workflow $workflow)
    {
        $this->authorize('update', $workflow);

        $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'trigger_type' => 'nullable|string|in:' . implode(',', Workflow::TRIGGER_TYPES),
            'trigger_conditions' => 'nullable|json',
            'is_active' => 'nullable|boolean',
            'execution_order' => 'nullable|integer',
        ]);

        $updated = $this->workflowService->updateWorkflow($workflow->id, $request->all());

        return response()->json([
            'success' => true,
            'data' => $updated,
            'message' => 'Workflow updated successfully',
        ]);
    }

    public function destroy(Workflow $workflow)
    {
        $this->authorize('delete', $workflow);

        $this->workflowService->deleteWorkflow($workflow->id);

        return response()->json([
            'success' => true,
            'message' => 'Workflow deleted successfully',
        ]);
    }

    public function addStep(Request $request, Workflow $workflow)
    {
        $this->authorize('update', $workflow);

        $request->validate([
            'step_number' => 'required|integer',
            'action_type' => 'required|string|in:' . implode(',', \App\Models\WorkflowStep::ACTION_TYPES),
            'action_config' => 'required|json',
            'conditions' => 'nullable|json',
            'is_conditional' => 'nullable|boolean',
            'wait_hours' => 'nullable|integer|min:0',
        ]);

        $step = $this->workflowService->createWorkflowStep(
            $workflow->id,
            $request->step_number,
            $request->all()
        );

        return response()->json([
            'success' => true,
            'data' => $step,
            'message' => 'Workflow step added successfully',
        ], 201);
    }

    public function updateStep(Request $request, Workflow $workflow, $stepId)
    {
        $this->authorize('update', $workflow);

        $step = $workflow->steps()->findOrFail($stepId);

        $request->validate([
            'action_type' => 'nullable|string|in:' . implode(',', \App\Models\WorkflowStep::ACTION_TYPES),
            'action_config' => 'nullable|json',
            'conditions' => 'nullable|json',
            'is_conditional' => 'nullable|boolean',
            'wait_hours' => 'nullable|integer|min:0',
        ]);

        $updated = $this->workflowService->updateWorkflowStep($stepId, $request->all());

        return response()->json([
            'success' => true,
            'data' => $updated,
            'message' => 'Workflow step updated successfully',
        ]);
    }

    public function removeStep(Workflow $workflow, $stepId)
    {
        $this->authorize('update', $workflow);

        $workflow->steps()->findOrFail($stepId);

        $this->workflowService->deleteWorkflowStep($stepId);

        return response()->json([
            'success' => true,
            'message' => 'Workflow step removed successfully',
        ]);
    }

    public function enable(Workflow $workflow)
    {
        $this->authorize('update', $workflow);

        $enabled = $this->workflowService->enableWorkflow($workflow->id);

        return response()->json([
            'success' => true,
            'data' => $enabled,
            'message' => 'Workflow enabled successfully',
        ]);
    }

    public function disable(Workflow $workflow)
    {
        $this->authorize('update', $workflow);

        $disabled = $this->workflowService->disableWorkflow($workflow->id);

        return response()->json([
            'success' => true,
            'data' => $disabled,
            'message' => 'Workflow disabled successfully',
        ]);
    }

    public function getExecutionHistory(Workflow $workflow, Request $request)
    {
        $this->authorize('view', $workflow);

        $limit = $request->query('limit', 50);
        $executions = $this->workflowService->getWorkflowExecutionHistory($workflow->id, $limit);

        return response()->json([
            'success' => true,
            'data' => $executions->items(),
            'pagination' => [
                'total' => $executions->total(),
                'per_page' => $executions->perPage(),
                'current_page' => $executions->currentPage(),
                'last_page' => $executions->lastPage(),
            ],
        ]);
    }

    public function getExecution(WorkflowExecution $execution)
    {
        $this->authorize('view', $execution->workflow);

        $details = $this->workflowService->getExecutionDetails($execution->id);

        return response()->json([
            'success' => true,
            'data' => $details,
        ]);
    }

    public function retryExecution(WorkflowExecution $execution)
    {
        $this->authorize('update', $execution->workflow);

        try {
            $retried = $this->workflowService->retryExecution($execution->id);

            return response()->json([
                'success' => true,
                'data' => $retried,
                'message' => 'Workflow execution queued for retry',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function testWorkflow(Request $request, Workflow $workflow)
    {
        $this->authorize('update', $workflow);

        $request->validate([
            'entity_type' => 'required|string|in:donor,patient,donation,appointment',
            'entity_data' => 'required|array',
        ]);

        // Create a test execution without actually running it
        $testExecution = [
            'workflow_id' => $workflow->id,
            'trigger_type' => $workflow->trigger_type,
            'entity_type' => $request->entity_type,
            'steps' => $workflow->steps()->get()->map(function ($step) {
                return [
                    'step_number' => $step->step_number,
                    'action_type' => $step->action_type,
                    'action_config' => $step->action_config,
                ];
            }),
        ];

        return response()->json([
            'success' => true,
            'data' => $testExecution,
            'message' => 'Workflow test data generated',
        ]);
    }

    public function triggerManually(Request $request)
    {
        $request->validate([
            'workflow_id' => 'required|exists:workflows,id',
            'entity_id' => 'required|integer',
            'entity_type' => 'required|string|in:donor,patient,donation,appointment',
        ]);

        $workflow = Workflow::findOrFail($request->workflow_id);
        $this->authorize('update', $workflow);

        // Get the entity
        $modelClass = 'App\\Models\\' . ucfirst($request->entity_type);
        $entity = $modelClass::findOrFail($request->entity_id);

        // Execute workflow
        $executions = $this->workflowService->executeWorkflow($entity, 'manual_trigger');

        return response()->json([
            'success' => true,
            'data' => $executions,
            'message' => 'Workflow triggered manually',
        ]);
    }

    public function getAvailableTriggers()
    {
        return response()->json([
            'success' => true,
            'data' => Workflow::TRIGGER_TYPES,
        ]);
    }

    public function getAvailableActions()
    {
        return response()->json([
            'success' => true,
            'data' => \App\Models\WorkflowStep::ACTION_TYPES,
        ]);
    }
}
