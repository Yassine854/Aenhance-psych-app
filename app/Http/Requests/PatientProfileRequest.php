<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PatientProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $spoofed = strtoupper((string) $this->input('_method', ''));
        $effectiveMethod = $spoofed !== '' ? $spoofed : strtoupper($this->method());
        $isCreate = $effectiveMethod === 'POST';

        return [
            'user_id' => ['nullable', 'exists:users,id'],
            'first_name' => [$isCreate ? 'required' : 'nullable', 'string', 'max:255'],
            'last_name' => [$isCreate ? 'required' : 'nullable', 'string', 'max:255'],
            'date_of_birth' => [$isCreate ? 'required' : 'nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:50'],
            'country' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'country_code' => ['nullable', 'string', 'max:10'],
            'profile_image_url' => ['nullable', 'string', 'max:1024'],

            'profile_image' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
