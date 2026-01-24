<?php

namespace App\Services;

use App\Models\Workflow;
use App\Models\WorkflowStep;
use App\Models\WorkflowExecution;

class WorkflowService
{
    public function createWorkflow($tenantId, array $data)
    {
        return Workflow::create([
            'tenant_id' => $tenantId,
            ...$data,
        ]);
    }

    public function updateWorkflow($workflowId, array $data)
    {
        $workflow = Workflow::findOrFail($workflowId);
        $workflow->update($data);

        return $workflow;
    }

    public function createWorkflowStep($workflowId, $stepNumber, array $data)
    {
        $workflow = Workflow::findOrFail($workflowId);

        return WorkflowStep::create([
            'tenant_id' => $workflow->tenant_id,
            'workflow_id' => $workflowId,
            'step_number' => $stepNumber,
            ...$data,
        ]);
    }

    public function updateWorkflowStep($stepId, array $data)
    {
        $step = WorkflowStep::findOrFail($stepId);
        $step->update($data);

        return $step;
    }

    public function deleteWorkflowStep($stepId)
    {
        return WorkflowStep::destroy($stepId);
    }

    public function deleteWorkflow($workflowId)
    {
        WorkflowStep::where('workflow_id', $workflowId)->delete();
        WorkflowExecution::where('workflow_id', $workflowId)->delete();

        return Workflow::destroy($workflowId);
    }

    public function enableWorkflow($workflowId)
    {
        return $this->updateWorkflow($workflowId, ['is_active' => true]);
    }

    public function disableWorkflow($workflowId)
    {
        return $this->updateWorkflow($workflowId, ['is_active' => false]);
    }

    public function executeWorkflow($entity, $triggerType)
    {
        $workflows = Workflow::forTrigger($triggerType)
            ->where('tenant_id', $entity->tenant_id)
            ->get();

        $executions = [];

        foreach ($workflows as $workflow) {
            $execution = $workflow->execute($entity, $triggerType);

            if ($execution) {
                $executions[] = $execution;
                
                // Dispatch job to process workflow
                dispatch(new \App\Jobs\ProcessWorkflowExecution($execution->id));
            }
        }

        return $executions;
    }

    public function processExecution($executionId)
    {
        $execution = WorkflowExecution::findOrFail($executionId);
        $workflow = $execution->workflow;

        $execution->markAsRunning();

        try {
            $steps = $workflow->steps()->orderBy('step_number')->get();

            foreach ($steps as $step) {
                if (!$step->shouldExecute($execution->entity())) {
                    continue;
                }

                $result = $this->executeStep($execution, $step);

                $execution->addCompletedStep($step->step_number, $result);

                if ($step->wait_hours > 0) {
                    // In a real scenario, you might want to pause execution here
                    sleep($step->wait_hours * 3600); // For demo purposes
                }
            }

            $execution->markAsCompleted();
        } catch (\Exception $e) {
            $execution->markAsFailed($e->getMessage());
        }

        return $execution;
    }

    private function executeStep($execution, $step)
    {
        $entity = $execution->entity();
        $config = $step->action_config;

        switch ($step->action_type) {
            case 'send_email':
                return $this->sendEmailAction($entity, $config);

            case 'send_sms':
                return $this->sendSmsAction($entity, $config);

            case 'create_task':
                return $this->createTaskAction($entity, $config);

            case 'defer_donor':
                return $this->deferDonorAction($entity, $config);

            case 'update_field':
                return $this->updateFieldAction($entity, $config);

            case 'create_notification':
                return $this->createNotificationAction($entity, $config);

            case 'add_tag':
                return $this->addTagAction($entity, $config);

            default:
                return ['status' => 'unknown_action'];
        }
    }

    private function sendEmailAction($entity, $config)
    {
        // Implement email sending logic
        \Illuminate\Support\Facades\Mail::to($entity->email ?? $entity->contact_email)
            ->send(new \App\Mail\GenericWorkflowEmail(
                $config['subject'] ?? 'Workflow Notification',
                $config['body'] ?? ''
            ));

        return ['status' => 'email_sent', 'recipient' => $entity->email];
    }

    private function sendSmsAction($entity, $config)
    {
        // Implement SMS sending logic
        if ($entity->phone) {
            \App\Services\SmsService::send(
                $entity->phone,
                $config['message'] ?? ''
            );
        }

        return ['status' => 'sms_sent', 'recipient' => $entity->phone];
    }

    private function createTaskAction($entity, $config)
    {
        // Create a task/reminder for staff
        // This would depend on your task management system
        $task = [
            'title' => $config['title'] ?? 'Workflow Task',
            'description' => $config['description'] ?? '',
            'assigned_to' => $config['assigned_to'] ?? null,
            'due_date' => $config['due_date'] ?? now()->addDays(1),
        ];

        return ['status' => 'task_created', 'task' => $task];
    }

    private function deferDonorAction($entity, $config)
    {
        if (method_exists($entity, 'defer')) {
            $entity->defer(
                $config['deferral_type'] ?? 'temporary',
                $config['reason'] ?? 'Workflow deferral',
                $config['until_date'] ?? null
            );
        }

        return ['status' => 'donor_deferred'];
    }

    private function updateFieldAction($entity, $config)
    {
        $field = $config['field'] ?? null;
        $value = $config['value'] ?? null;

        if ($field && $value) {
            $entity->update([$field => $value]);
        }

        return ['status' => 'field_updated', 'field' => $field];
    }

    private function createNotificationAction($entity, $config)
    {
        // Create in-app notification
        $notification = [
            'title' => $config['title'] ?? 'Notification',
            'message' => $config['message'] ?? '',
            'type' => $config['type'] ?? 'info',
        ];

        return ['status' => 'notification_created', 'notification' => $notification];
    }

    private function addTagAction($entity, $config)
    {
        $tag = $config['tag'] ?? null;

        if ($tag && method_exists($entity, 'addTag')) {
            $entity->addTag($tag);
        }

        return ['status' => 'tag_added', 'tag' => $tag];
    }

    public function listWorkflows($tenantId)
    {
        return Workflow::where('tenant_id', $tenantId)
            ->orderBy('execution_order')
            ->get();
    }

    public function getWorkflowExecutionHistory($workflowId, $limit = 50)
    {
        return WorkflowExecution::where('workflow_id', $workflowId)
            ->latest()
            ->paginate($limit);
    }

    public function getExecutionDetails($executionId)
    {
        $execution = WorkflowExecution::findOrFail($executionId);

        return [
            'id' => $execution->id,
            'workflow_id' => $execution->workflow_id,
            'entity' => $execution->entity(),
            'status' => $execution->status,
            'progress' => $execution->getExecutionProgress(),
            'log' => $execution->execution_log,
            'started_at' => $execution->started_at,
            'completed_at' => $execution->completed_at,
        ];
    }

    public function retryExecution($executionId)
    {
        $execution = WorkflowExecution::findOrFail($executionId);

        if ($execution->status !== 'failed') {
            throw new \Exception('Only failed executions can be retried');
        }

        $execution->update([
            'status' => 'pending',
            'error_message' => null,
            'completed_steps' => null,
        ]);

        dispatch(new \App\Jobs\ProcessWorkflowExecution($executionId));

        return $execution;
    }
}
