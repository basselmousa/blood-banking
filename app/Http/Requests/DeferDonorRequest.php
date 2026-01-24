<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeferDonorRequest extends FormRequest
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
            'reason' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:1000'],
            'deferral_type' => ['required', 'in:temporary,permanent,conditional'],
            'eligible_after' => ['nullable', 'date', 'after:today'],
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'reason.required' => 'Please provide a deferral reason',
            'description.required' => 'Please provide a detailed description',
            'deferral_type.required' => 'Please select a deferral type',
            'eligible_after.date' => 'The eligible date must be a valid date',
            'eligible_after.after' => 'The eligible date must be in the future',
        ];
    }
}
