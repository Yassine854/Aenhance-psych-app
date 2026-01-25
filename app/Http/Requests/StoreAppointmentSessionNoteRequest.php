<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentSessionNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'appointment_session_id' => ['required', 'integer', 'exists:appointment_sessions,id'],
            'session_date' => ['required', 'date'],
            'session_duration' => ['required', 'integer', 'min:0'],
            'session_mode' => ['required', 'string', 'in:video_audio,audio,video'],
            'risk_level' => ['required', 'string', 'in:none,low,medium,high,very_high'],
            'subjective' => ['nullable', 'string', 'max:5000'],
            'objective' => ['nullable', 'string', 'max:5000'],
            'assessment' => ['nullable', 'string', 'max:5000'],
            'intervention' => ['nullable', 'string', 'max:5000'],
            'plan' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
