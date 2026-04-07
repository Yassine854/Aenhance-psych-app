<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PsychologistDiploma extends Model
{
    use HasFactory;

    protected $fillable = [
        'psychologist_profile_id',
        'file_url',
    ];

    public function profile()
    {
        return $this->belongsTo(PsychologistProfile::class, 'psychologist_profile_id');
    }

    public function getFileUrlAttribute($value)
    {
        if (! $value) return null;

        if (preg_match('#^https?://#', $value)) {
            return $value;
        }

        $disk = config('app.avatar_disk', 'public');

        $path = $value;
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

    public function deleteFile()
    {
        $value = $this->getAttributes()['file_url'] ?? null;
        if (! $value) return false;

        $disk = config('app.avatar_disk', 'public');
        $candidates = [$value];

        if (str_starts_with($value, '/storage/')) {
            $candidates[] = preg_replace('#^/storage/#', '', $value);
        }
        if (str_starts_with($value, 'storage/app/public/')) {
            $candidates[] = preg_replace('#^storage/app/public/#', '', $value);
        }
        if (preg_match('#^https?://#', $value)) {
            $path = parse_url($value, PHP_URL_PATH) ?: '';
            if ($path) {
                $candidates[] = preg_replace('#^.*/storage/#', '', $path);
            }
        }

        foreach ($candidates as $candidate) {
            if (! $candidate) continue;
            try {
                if (Storage::disk($disk)->exists($candidate)) {
                    return Storage::disk($disk)->delete($candidate);
                }
            } catch (\Throwable $e) {
                // Continue trying normalized candidates.
            }
        }

        return false;
    }
}
