<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SitePage extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'title_en', 'title_fr', 'title_ar',
        'content_en', 'content_fr', 'content_ar',
        'created_by', 'updated_by',
    ];

    public static function getByKey(string $key)
    {
        return static::where('key', $key)->first();
    }
}
