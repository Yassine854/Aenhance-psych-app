<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RessourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) ($user && method_exists($user, 'isAdmin') && $user->isAdmin());
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => is_string($this->title) ? trim($this->title) : $this->title,
            'slug' => is_string($this->slug) ? trim($this->slug) : $this->slug,
            'description' => is_string($this->description) ? trim($this->description) : $this->description,
        ]);
    }

    public function rules(): array
    {
        $ressource = $this->route('ressource');
        $ressourceId = $ressource?->id;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('ressources', 'slug')->ignore($ressourceId)],
            'description' => ['nullable', 'string', 'max:3000'],
            'pdf' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
            'published_at' => ['nullable', 'date'],
            'remove_pdf' => ['nullable', 'boolean'],
        ];
    }
}