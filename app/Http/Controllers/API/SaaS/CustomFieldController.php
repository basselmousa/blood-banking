<?php

namespace App\Http\Controllers\API\SaaS;

use App\Http\Controllers\Controller;
use App\Models\CustomField;
use App\Services\CustomFieldService;
use Illuminate\Http\Request;

class CustomFieldController extends Controller
{
    protected $fieldService;

    public function __construct(CustomFieldService $fieldService)
    {
        $this->fieldService = $fieldService;
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        $request->validate([
            'entity_type' => 'required|string|in:donor,donation,appointment,patient',
        ]);

        $tenantId = auth()->user()->current_tenant_id;

        $fields = $this->fieldService->getCustomFieldsForEntity(
            $tenantId,
            $request->entity_type
        );

        return response()->json([
            'success' => true,
            'data' => $fields,
            'count' => $fields->count(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'entity_type' => 'required|string|in:donor,donation,appointment,patient',
            'field_name' => 'required|string|max:255',
            'field_label' => 'required|string|max:255',
            'field_type' => 'required|string|in:' . implode(',', CustomField::FIELD_TYPES),
            'field_options' => 'nullable|json',
            'validation_rules' => 'nullable|json',
            'default_value' => 'nullable',
            'is_required' => 'nullable|boolean',
            'is_visible' => 'nullable|boolean',
            'is_searchable' => 'nullable|boolean',
            'conditional_logic' => 'nullable|json',
            'help_text' => 'nullable|string',
        ]);

        $tenantId = auth()->user()->current_tenant_id;

        $field = $this->fieldService->createCustomField($tenantId, $request->all());

        return response()->json([
            'success' => true,
            'data' => $field,
            'message' => 'Custom field created successfully',
        ], 201);
    }

    public function show(CustomField $customField)
    {
        $this->authorize('view', $customField);

        return response()->json([
            'success' => true,
            'data' => $customField,
        ]);
    }

    public function update(Request $request, CustomField $customField)
    {
        $this->authorize('update', $customField);

        $request->validate([
            'field_label' => 'nullable|string|max:255',
            'field_options' => 'nullable|json',
            'validation_rules' => 'nullable|json',
            'default_value' => 'nullable',
            'is_required' => 'nullable|boolean',
            'is_visible' => 'nullable|boolean',
            'is_searchable' => 'nullable|boolean',
            'conditional_logic' => 'nullable|json',
            'help_text' => 'nullable|string',
        ]);

        $field = $this->fieldService->updateCustomField($customField->id, $request->all());

        return response()->json([
            'success' => true,
            'data' => $field,
            'message' => 'Custom field updated successfully',
        ]);
    }

    public function destroy(CustomField $customField)
    {
        $this->authorize('delete', $customField);

        $this->fieldService->deleteCustomField($customField->id);

        return response()->json([
            'success' => true,
            'message' => 'Custom field deleted successfully',
        ]);
    }

    public function validateValue(Request $request)
    {
        $request->validate([
            'field_id' => 'required|exists:custom_fields,id',
            'value' => 'nullable',
        ]);

        $field = CustomField::findOrFail($request->field_id);

        $validation = $this->fieldService->validateCustomFieldValue($field, $request->value);

        return response()->json([
            'success' => $validation['valid'],
            'valid' => $validation['valid'],
            'error' => $validation['error'] ?? null,
        ]);
    }

    public function validateBulk(Request $request)
    {
        $request->validate([
            'entity_type' => 'required|string|in:donor,donation,appointment,patient',
            'data' => 'required|array',
        ]);

        $tenantId = auth()->user()->current_tenant_id;

        $validation = $this->fieldService->validateCustomFields(
            $tenantId,
            $request->entity_type,
            $request->data
        );

        return response()->json([
            'success' => $validation['valid'],
            'valid' => $validation['valid'],
            'errors' => $validation['errors'],
        ]);
    }

    public function getVisibleFields(Request $request)
    {
        $request->validate([
            'entity_type' => 'required|string|in:donor,donation,appointment,patient',
            'parent_data' => 'nullable|array',
        ]);

        $tenantId = auth()->user()->current_tenant_id;

        $fields = $this->fieldService->filterVisibleFields(
            $tenantId,
            $request->entity_type,
            $request->parent_data ?? []
        );

        return response()->json([
            'success' => true,
            'data' => $fields->values(),
            'count' => $fields->count(),
        ]);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'entity_type' => 'required|string|in:donor,donation,appointment,patient',
            'field_order' => 'required|array',
            'field_order.*' => 'integer|exists:custom_fields,id',
        ]);

        $tenantId = auth()->user()->current_tenant_id;

        $this->fieldService->reorderFields(
            $tenantId,
            $request->entity_type,
            $request->field_order
        );

        return response()->json([
            'success' => true,
            'message' => 'Fields reordered successfully',
        ]);
    }

    public function bulkCreate(Request $request)
    {
        $request->validate([
            'entity_type' => 'required|string|in:donor,donation,appointment,patient',
            'fields' => 'required|array',
            'fields.*' => 'array',
            'fields.*.field_name' => 'required|string',
            'fields.*.field_label' => 'required|string',
            'fields.*.field_type' => 'required|string|in:' . implode(',', CustomField::FIELD_TYPES),
        ]);

        $tenantId = auth()->user()->current_tenant_id;

        $fields = $this->fieldService->bulkCreateFields(
            $tenantId,
            $request->entity_type,
            $request->fields
        );

        return response()->json([
            'success' => true,
            'data' => $fields,
            'count' => count($fields),
            'message' => 'Custom fields created successfully',
        ], 201);
    }
}
