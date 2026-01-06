<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class PsychologistProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null; // require auth; refine as needed
    }

    protected function prepareForValidation(): void
    {
        // Admin Create.vue posts availabilities as JSON in FormData.
        $raw = $this->input('availabilities');
        if (is_string($raw) && $raw !== '') {
            try {
                $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
                $this->merge(['availabilities' => $decoded]);
            } catch (\Throwable $e) {
                // Leave as-is; validation will surface a clean error.
            }
        }

        // Some clients may send languages as a JSON string in FormData.
        $rawLang = $this->input('languages');
        if (is_string($rawLang) && $rawLang !== '') {
            try {
                $decoded = json_decode($rawLang, true, 512, JSON_THROW_ON_ERROR);
                if (is_array($decoded)) {
                    $this->merge(['languages' => $decoded]);
                }
            } catch (\Throwable $e) {
                // Leave as-is; validation will surface a clean error.
            }
        }
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
            'languages' => ['required', 'array', 'min:1'],
            'languages.*' => ['required', 'string', 'distinct', 'in:english,french,arabic'],
            'specialisation_ids' => [$isCreate ? 'required' : 'nullable', 'array', 'min:1'],
            'specialisation_ids.*' => ['integer', 'distinct', 'exists:specialisations,id'],
            'phone' => [$isCreate ? 'required' : 'nullable', 'string', 'max:50'],
            'country_code' => ['nullable', 'string', 'max:10'],
            'diploma' => ['nullable', 'string', 'max:1024'],
            'cin' => ['nullable', 'string', 'max:1024'],
            'cv' => ['nullable', 'string', 'max:1024'],
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
            'cv_file' => [$isCreate ? 'required' : 'nullable', 'mimes:pdf', 'max:5120'],

            // Weekly availability slots
            'availabilities' => ['nullable', 'array'],
            'availabilities.*.day_of_week' => ['required', 'integer', 'between:0,6'],
            'availabilities.*.start_time' => ['required', 'date_format:H:i'],
            'availabilities.*.end_time' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    // attribute looks like availabilities.0.end_time
                    $parts = explode('.', (string) $attribute);
                    $index = $parts[1] ?? null;
                    if ($index === null) {
                        return;
                    }

                    $slots = $this->input('availabilities', []);
                    $start = Arr::get($slots, $index . '.start_time');
                    if (!is_string($start) || !is_string($value)) {
                        return;
                    }

                    $startTs = strtotime('1970-01-01 ' . $start);
                    $endTs = strtotime('1970-01-01 ' . $value);
                    if ($startTs === false || $endTs === false) {
                        return;
                    }

                    if ($endTs <= $startTs) {
                        $fail('The end time must be after the start time.');
                    }
                },
            ],
        ];
    }
}
