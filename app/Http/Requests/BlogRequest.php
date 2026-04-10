<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BlogRequest extends FormRequest
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
            'excerpt' => is_string($this->excerpt) ? trim($this->excerpt) : $this->excerpt,
            'category' => is_string($this->category) ? trim($this->category) : $this->category,
            'meta_title' => is_string($this->meta_title) ? trim($this->meta_title) : $this->meta_title,
            'meta_description' => is_string($this->meta_description) ? trim($this->meta_description) : $this->meta_description,
            'featured_image_alt' => is_string($this->featured_image_alt) ? trim($this->featured_image_alt) : $this->featured_image_alt,
        ]);
    }

    public function rules(): array
    {
        $blog = $this->route('blog');
        $blogId = $blog?->id;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('blogs', 'slug')->ignore($blogId)],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'content' => ['required', 'string'],
            'featured_image' => ['nullable', 'image', 'max:5120'],
            'featured_image_alt' => ['nullable', 'string', 'max:255'],
            'published_at' => ['nullable', 'date'],
            'category' => ['nullable', 'string', 'max:100'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'remove_featured_image' => ['nullable', 'boolean'],
        ];
    }
}