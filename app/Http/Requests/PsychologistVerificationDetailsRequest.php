<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PsychologistVerificationDetailsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'psychologist_profile_id' => 'required|exists:psychologist_profiles,id',
            'rib' => 'required|string|max:255',
            'bank_name' => 'required|string|max:255',
            'bank_account_number' => 'required|string|max:255',
            'bank_account_name' => 'required|string|max:255',
            'rib_file_url' => 'required|string|max:255',
            'identity_type' => 'required|string|max:255',
            'identity_number' => 'required|string|max:255',
            'identity_file_url' => 'required|string|max:255',
            'rejection_reason' => 'nullable|string',
            'verification_status' => 'sometimes|in:pending,approved,rejected',
        ];
    }
}