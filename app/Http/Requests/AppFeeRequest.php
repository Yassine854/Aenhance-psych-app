<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AppFeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'percentage' => ['required', 'numeric', 'between:0,100'],
        ];
    }
}
