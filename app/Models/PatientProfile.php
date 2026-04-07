<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PatientProfile extends Model
{
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'country',
        'city',
        'phone',
        'country_code',
        'profile_image_url',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getProfileImageUrlAttribute($value)
    {
        if (! $value) return null;

        if (preg_match('#^https?://#', $value)) {
            return $value;
        }

        $disk = config('app.avatar_disk', 'public');

        $path = $value;
        // Normalize values like '/storage/avatars/..' to 'avatars/...'
        if (str_starts_with($path, '/storage/')) {
            $path = preg_replace('#^/storage/#', '', $path);
        } elseif (str_starts_with($path, 'storage/app/public/')) {
            $path = preg_replace('#^storage/app/public/#', '', $path);
        }

        try {
            return Storage::disk($disk)->url($path);
        } catch (\Throwable $e) {
            return $value;
        }
    }

    public function deleteProfileImageFile()
    {
        $value = $this->getAttributes()['profile_image_url'] ?? null;
        if (! $value) return false;

        $disk = config('app.avatar_disk', 'public');

        $candidates = [$value];
        if (str_starts_with($value, '/storage/')) {
            $candidates[] = preg_replace('#^/storage/#', '', $value);
        }
        if (str_starts_with($value, 'storage/app/public/')) {
            $candidates[] = preg_replace('#^storage/app/public/#', '', $value);
        }
        // parse path if a full URL was stored
        if (preg_match('#^https?://#', $value)) {
            $p = parse_url($value, PHP_URL_PATH) ?: '';
            if ($p) $candidates[] = preg_replace('#^.*/storage/#', '', $p);
        }

        foreach ($candidates as $candidate) {
            if (! $candidate) continue;
            try {
                if (Storage::disk($disk)->exists($candidate)) {
                    return Storage::disk($disk)->delete($candidate);
                }
            } catch (\Throwable $e) {
                // continue trying other candidates
            }
        }

        return false;
    }
}
