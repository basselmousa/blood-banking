<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomField extends Model
{
    use \App\Traits\BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'entity_type',
        'field_name',
        'field_label',
        'field_type',
        'field_options',
        'validation_rules',
        'default_value',
        'is_required',
        'is_visible',
        'is_searchable',
        'sort_order',
        'conditional_logic',
        'help_text',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_visible' => 'boolean',
        'is_searchable' => 'boolean',
        'field_options' => 'json',
        'validation_rules' => 'json',
        'conditional_logic' => 'json',
    ];

    public const FIELD_TYPES = [
        'text',
        'email',
        'phone',
        'select',
        'multiselect',
        'date',
        'datetime',
        'textarea',
        'checkbox',
        'radio',
        'url',
        'number',
    ];

    public const ENTITY_TYPES = [
        'donor',
        'donation',
        'appointment',
        'patient',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function validate($value)
    {
        if ($this->is_required && empty($value)) {
            return false;
        }

        if ($this->validation_rules) {
            foreach ($this->validation_rules as $rule => $options) {
                if (!$this->validateRule($rule, $value, $options)) {
                    return false;
                }
            }
        }

        return true;
    }

    private function validateRule($rule, $value, $options)
    {
        switch ($rule) {
            case 'min_length':
                return strlen($value) >= $options;
            case 'max_length':
                return strlen($value) <= $options;
            case 'pattern':
                return preg_match($options, $value);
            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL);
            case 'url':
                return filter_var($value, FILTER_VALIDATE_URL);
            case 'numeric':
                return is_numeric($value);
            default:
                return true;
        }
    }

    public function shouldDisplay($parentData = [])
    {
        if (!$this->conditional_logic) {
            return true;
        }

        foreach ($this->conditional_logic as $condition) {
            $fieldName = $condition['field_name'] ?? null;
            $operator = $condition['operator'] ?? '=';
            $value = $condition['value'] ?? null;

            if ($fieldName && isset($parentData[$fieldName])) {
                $parentValue = $parentData[$fieldName];

                switch ($operator) {
                    case '=':
                        if ($parentValue != $value) return false;
                        break;
                    case '!=':
                        if ($parentValue == $value) return false;
                        break;
                    case 'contains':
                        if (strpos($parentValue, $value) === false) return false;
                        break;
                    case 'in':
                        if (!in_array($parentValue, $value)) return false;
                        break;
                }
            }
        }

        return true;
    }

    public function scopeForEntity($query, $entityType)
    {
        return $query->where('entity_type', $entityType)->where('is_visible', true)->orderBy('sort_order');
    }

    public function scopeSearchable($query)
    {
        return $query->where('is_searchable', true);
    }
}
