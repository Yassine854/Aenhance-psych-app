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
        return [
            'user_id' => ['nullable', 'exists:users,id'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'specialization' => ['required', 'string', 'max:255'],
            'diploma' => ['nullable', 'string', 'max:1024'],
            'cin' => ['nullable', 'string', 'max:1024'],
            'gender' => ['nullable', 'string', 'max:50'],
            'country' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date'],
            'bio' => ['nullable', 'string'],
            'price_per_session' => ['required', 'numeric', 'min:0'],
            'is_approved' => ['boolean'],
            'profile_image_url' => ['nullable', 'string', 'max:1024'],

            // File uploads (handled server-side via Cloudinary)
            'profile_image' => ['nullable', 'image', 'max:2048'],
            'diploma_file' => ['nullable', 'mimes:pdf', 'max:5120'],
            'cin_file' => ['nullable', 'mimes:pdf', 'max:5120'],
        ];
    }
}
