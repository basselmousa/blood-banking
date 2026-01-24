<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workflow extends Model
{
    use \App\Traits\BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'trigger_type',
        'trigger_conditions',
        'is_active',
        'execution_order',
    ];

    protected $casts = [
        'trigger_conditions' => 'json',
        'is_active' => 'boolean',
    ];

    public const TRIGGER_TYPES = [
        'donor.created',
        'donor.updated',
        'donation.recorded',
        'donation.completed',
        'appointment.scheduled',
        'appointment.completed',
        'patient.created',
        'eligibility.failed',
        'deferral.created',
        'manual_trigger',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(WorkflowStep::class)->orderBy('step_number');
    }

    public function executions(): HasMany
    {
        return $this->hasMany(WorkflowExecution::class);
    }

    public function shouldTrigger($entity, $triggerType)
    {
        if ($this->trigger_type !== $triggerType || !$this->is_active) {
            return false;
        }

        if ($this->trigger_conditions) {
            return $this->evaluateConditions($entity, $this->trigger_conditions);
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
                case 'contains':
                    if (strpos($entityValue, $value) === false) return false;
                    break;
                case 'in':
                    if (!in_array($entityValue, $value)) return false;
                    break;
            }
        }

        return true;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForTrigger($query, $triggerType)
    {
        return $query->where('trigger_type', $triggerType)->where('is_active', true);
    }

    public function execute($entity, $triggerType)
    {
        if (!$this->shouldTrigger($entity, $triggerType)) {
            return null;
        }

        return WorkflowExecution::create([
            'workflow_id' => $this->id,
            'entity_type' => class_basename($entity),
            'entity_id' => $entity->id,
            'status' => 'pending',
            'execution_log' => json_encode(['initiated_at' => now()]),
        ]);
    }
}
