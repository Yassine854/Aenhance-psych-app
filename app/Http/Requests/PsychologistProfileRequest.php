<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PsychologistProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null; // require auth; refine as needed
    }

    public function rules(): array
    {
        // For create we require key fields; for update we make them nullable.
        // Be robust to method spoofing when the client sends POST with _method=PUT/PATCH.
        $spoofed = strtoupper((string) $this->input('_method', ''));
        $effectiveMethod = $spoofed !== '' ? $spoofed : strtoupper($this->method());
        $isCreate = $effectiveMethod === 'POST';

        return [
            'user_id' => ['nullable', 'exists:users,id'],
            'first_name' => [$isCreate ? 'required' : 'nullable', 'string', 'max:255'],
            'last_name' => [$isCreate ? 'required' : 'nullable', 'string', 'max:255'],
            'specialization' => [$isCreate ? 'required' : 'nullable', 'string', 'max:255'],
            'phone' => [$isCreate ? 'required' : 'nullable', 'string', 'max:50'],
            'country_code' => ['nullable', 'string', 'max:10'],
            'diploma' => ['nullable', 'string', 'max:1024'],
            'cin' => ['nullable', 'string', 'max:1024'],
            'gender' => ['nullable', 'string', 'max:50'],
            'country' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date'],
            'bio' => ['nullable', 'string'],
            'price_per_session' => [$isCreate ? 'required' : 'nullable', 'numeric', 'min:0'],
            'is_approved' => ['nullable', 'boolean'],
            'profile_image_url' => ['nullable', 'string', 'max:1024'],

            // File uploads (required on create, optional on update)
            'profile_image' => ['nullable', 'image', 'max:2048'],
            'diploma_file' => [$isCreate ? 'required' : 'nullable', 'mimes:pdf', 'max:5120'],
            'cin_file' => [$isCreate ? 'required' : 'nullable', 'mimes:pdf', 'max:5120'],
        ];
    }
}
