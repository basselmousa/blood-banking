<?php

namespace App\Services;

use App\Models\CustomField;

class CustomFieldService
{
    public function createCustomField($tenantId, array $data)
    {
        return CustomField::create([
            'tenant_id' => $tenantId,
            ...$data,
        ]);
    }

    public function updateCustomField($fieldId, array $data)
    {
        $field = CustomField::findOrFail($fieldId);
        $field->update($data);

        return $field;
    }

    public function deleteCustomField($fieldId)
    {
        return CustomField::destroy($fieldId);
    }

    public function getCustomFieldsForEntity($tenantId, $entityType)
    {
        return CustomField::where('tenant_id', $tenantId)
            ->where('entity_type', $entityType)
            ->where('is_visible', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function getSearchableFieldsForEntity($tenantId, $entityType)
    {
        return CustomField::where('tenant_id', $tenantId)
            ->where('entity_type', $entityType)
            ->where('is_searchable', true)
            ->get();
    }

    public function validateCustomFieldValue($field, $value)
    {
        if ($field->is_required && empty($value)) {
            return [
                'valid' => false,
                'error' => "{$field->field_label} is required",
            ];
        }

        if (empty($value)) {
            return ['valid' => true];
        }

        switch ($field->field_type) {
            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return [
                        'valid' => false,
                        'error' => "{$field->field_label} must be a valid email",
                    ];
                }
                break;

            case 'phone':
                if (!preg_match('/^\+?[0-9\s\-\(\)]+$/', $value)) {
                    return [
                        'valid' => false,
                        'error' => "{$field->field_label} must be a valid phone number",
                    ];
                }
                break;

            case 'url':
                if (!filter_var($value, FILTER_VALIDATE_URL)) {
                    return [
                        'valid' => false,
                        'error' => "{$field->field_label} must be a valid URL",
                    ];
                }
                break;

            case 'number':
                if (!is_numeric($value)) {
                    return [
                        'valid' => false,
                        'error' => "{$field->field_label} must be a number",
                    ];
                }
                break;

            case 'date':
                if (!strtotime($value)) {
                    return [
                        'valid' => false,
                        'error' => "{$field->field_label} must be a valid date",
                    ];
                }
                break;

            case 'select':
            case 'radio':
                if ($field->field_options && !in_array($value, $field->field_options)) {
                    return [
                        'valid' => false,
                        'error' => "{$field->field_label} has an invalid selection",
                    ];
                }
                break;
        }

        return ['valid' => true];
    }

    public function validateCustomFields($tenantId, $entityType, $data)
    {
        $fields = $this->getCustomFieldsForEntity($tenantId, $entityType);
        $errors = [];

        foreach ($fields as $field) {
            if (isset($data[$field->field_name])) {
                $validation = $this->validateCustomFieldValue($field, $data[$field->field_name]);

                if (!$validation['valid']) {
                    $errors[$field->field_name] = $validation['error'];
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    public function filterVisibleFields($tenantId, $entityType, $parentData = [])
    {
        $fields = $this->getCustomFieldsForEntity($tenantId, $entityType);

        return $fields->filter(function ($field) use ($parentData) {
            return $field->shouldDisplay($parentData);
        });
    }

    public function reorderFields($tenantId, $entityType, $fieldOrder)
    {
        foreach ($fieldOrder as $index => $fieldId) {
            CustomField::where('id', $fieldId)
                ->where('tenant_id', $tenantId)
                ->update(['sort_order' => $index]);
        }

        return true;
    }

    public function bulkCreateFields($tenantId, $entityType, array $fieldDefinitions)
    {
        $fields = [];

        foreach ($fieldDefinitions as $index => $definition) {
            $fields[] = $this->createCustomField($tenantId, [
                'entity_type' => $entityType,
                'sort_order' => $index,
                ...$definition,
            ]);
        }

        return $fields;
    }
}
