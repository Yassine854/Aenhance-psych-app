<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && ($this->user()->isPatient() || $this->user()->isPsychologist());
    }

    public function rules(): array
    {
        return [
            'reported_id' => ['required', 'integer'],
            'reported_type' => ['required', 'string', 'in:patient,psychologist'],
            'reason' => ['required', 'string', 'max:2000'],
            'proof_image' => ['nullable', 'image', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'A reason for the report is required.',
            'proof_image.image' => 'Proof must be an image file.',
        ];
    }
}
