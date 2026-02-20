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
        $adultCutoff = now()->subYears(18)->toDateString();

        return [
            'user_id' => ['nullable', 'exists:users,id'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:'.$adultCutoff],
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
