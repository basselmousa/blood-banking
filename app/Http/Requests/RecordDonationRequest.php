<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecordDonationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'donation_date' => ['required', 'date'],
            'blood_volume' => ['nullable', 'integer', 'min:100', 'max:500'],
            'type' => ['required', 'in:whole_blood,plasma,platelets,red_cells'],
            'status' => ['required', 'in:completed,rejected,deferred,cancelled'],
            'rejection_reason' => ['nullable', 'required_if:status,rejected', 'string', 'max:1000'],
            'hemoglobin_before' => ['nullable', 'numeric', 'min:8', 'max:20'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'donation_date.required' => 'Donation date is required',
            'type.required' => 'Please select a donation type',
            'status.required' => 'Please select a donation status',
            'rejection_reason.required_if' => 'Rejection reason is required when status is rejected',
            'blood_volume.min' => 'Blood volume must be at least 100ml',
            'blood_volume.max' => 'Blood volume cannot exceed 500ml',
            'hemoglobin_before.numeric' => 'Hemoglobin level must be a number',
        ];
    }
}
