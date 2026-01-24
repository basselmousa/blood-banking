<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowStep extends Model
{
    use \App\Traits\BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'workflow_id',
        'step_number',
        'action_type',
        'action_config',
        'conditions',
        'is_conditional',
        'wait_hours',
    ];

    protected $casts = [
        'action_config' => 'json',
        'conditions' => 'json',
        'is_conditional' => 'boolean',
    ];

    public const ACTION_TYPES = [
        'send_email',
        'send_sms',
        'create_task',
        'defer_donor',
        'update_field',
        'create_notification',
        'trigger_webhook',
        'call_api',
        'add_tag',
        'remove_tag',
        'change_status',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function shouldExecute($entity, $stepData = [])
    {
        if ($this->is_conditional && $this->conditions) {
            return $this->evaluateConditions($entity, $this->conditions);
        }

        return true;
    }

    private function evaluateConditions($entity, $conditions)
    {
        foreach ($conditions as $condition) {
            $field = $condition['field'] ?? null;
            $operator = $condition['operator'] ?? '=';
            $value = $condition['value'] ?? null;

            if (!$field) continue;

            $entityValue = $entity->{$field} ?? null;

            switch ($operator) {
                case '=':
                    if ($entityValue != $value) return false;
                    break;
                case '!=':
                    if ($entityValue == $value) return false;
                    break;
                case '>':
                    if ($entityValue <= $value) return false;
                    break;
                case '<':
                    if ($entityValue >= $value) return false;
                    break;
            }
        }

        return true;
    }

    public function getActionDetails()
    {
        return [
            'type' => $this->action_type,
            'config' => $this->action_config,
            'should_wait' => $this->wait_hours > 0,
            'wait_until' => $this->wait_hours > 0 ? now()->addHours($this->wait_hours) : null,
        ];
    }
}
