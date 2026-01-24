<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListEligibleDonorsRequest extends FormRequest
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
            'blood_group' => ['required', 'in:O+,O-,A+,A-,B+,B-,AB+,AB-'],
            'city' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'blood_group.required' => 'Please select a blood group',
            'blood_group.in' => 'Invalid blood group selected',
        ];
    }
}
