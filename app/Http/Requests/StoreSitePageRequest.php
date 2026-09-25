<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSitePageRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules()
    {
        return [
            'key' => ['required', 'string', 'max:191', 'unique:site_pages,key'],
            'title_en' => ['nullable', 'string', 'max:191'],
            'title_fr' => ['nullable', 'string', 'max:191'],
            'title_ar' => ['nullable', 'string', 'max:191'],
            'content_en' => ['required', 'string'],
            'content_fr' => ['required', 'string'],
            'content_ar' => ['required', 'string'],
            'identifier' => ['nullable', 'integer', 'min:0', 'max:255', 'unique:site_pages,identifier'],
        ];
    }
}
