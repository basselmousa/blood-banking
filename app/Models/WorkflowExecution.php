<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowExecution extends Model
{
    use \App\Traits\BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'workflow_id',
        'entity_type',
        'entity_id',
        'status',
        'completed_steps',
        'error_message',
        'execution_log',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'execution_log' => 'json',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public const STATUSES = [
        'pending',
        'running',
        'completed',
        'failed',
        'paused',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function logEvent($event, $details = [])
    {
        $log = $this->execution_log ?? [];
        if (!is_array($log)) {
            $log = [];
        }

        $log[] = array_merge([
            'timestamp' => now()->toIso8601String(),
            'event' => $event,
        ], $details);

        $this->execution_log = $log;
        $this->save();
    }

    public function addCompletedStep($stepNumber, $result = null)
    {
        $completed = $this->completed_steps ?? [];
        if (!is_array($completed)) {
            $completed = [];
        }

        $completed[] = [
            'step' => $stepNumber,
            'completed_at' => now()->toIso8601String(),
            'result' => $result,
        ];

        $this->completed_steps = $completed;
        $this->save();

        $this->logEvent('step_completed', ['step_number' => $stepNumber]);
    }

    public function markAsRunning()
    {
        $this->update([
            'status' => 'running',
            'started_at' => now(),
        ]);

        $this->logEvent('execution_started');
    }

    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $this->logEvent('execution_completed');
    }

    public function markAsFailed($errorMessage = null)
    {
        $this->update([
            'status' => 'failed',
            'completed_at' => now(),
            'error_message' => $errorMessage,
        ]);

        $this->logEvent('execution_failed', ['error' => $errorMessage]);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function getExecutionProgress()
    {
        $totalSteps = $this->workflow->steps()->count();
        $completedSteps = is_array($this->completed_steps) ? count($this->completed_steps) : 0;

        return [
            'total_steps' => $totalSteps,
            'completed_steps' => $completedSteps,
            'progress_percentage' => $totalSteps > 0 ? round(($completedSteps / $totalSteps) * 100) : 0,
            'status' => $this->status,
        ];
    }
}
