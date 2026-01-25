<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentSessionNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'session_date' => ['sometimes', 'date'],
            'session_duration' => ['sometimes', 'integer', 'min:0'],
            'session_mode' => ['sometimes', 'string', 'in:video_audio,audio,video'],
            'risk_level' => ['sometimes', 'string', 'in:none,low,medium,high,very_high'],
            'subjective' => ['nullable', 'string', 'max:5000'],
            'objective' => ['nullable', 'string', 'max:5000'],
            'assessment' => ['nullable', 'string', 'max:5000'],
            'intervention' => ['nullable', 'string', 'max:5000'],
            'plan' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
