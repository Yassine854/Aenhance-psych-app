<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSitePageRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules()
    {
        // support both resource param names: 'page' (routes resource) and legacy 'site_page'
        if ($this->route('page')) {
            $id = $this->route('page')->id;
        } elseif ($this->route('site_page')) {
            $id = $this->route('site_page')->id;
        } else {
            $id = null;
        }

        return [
            'key' => ['required', 'string', 'max:191', 'unique:site_pages,key,' . $id],
            'title_en' => ['nullable', 'string', 'max:191'],
            'title_fr' => ['nullable', 'string', 'max:191'],
            'title_ar' => ['nullable', 'string', 'max:191'],
            'content_en' => ['required', 'string'],
            'content_fr' => ['required', 'string'],
            'content_ar' => ['required', 'string'],
        ];
    }
}
