<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PsychologistProfile extends Model
{
    use HasFactory;

    protected $with = ['diplomas'];
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'languages',
        'phone',
        'country_code',
        // diplomas moved to separate table
        'cv',
        'gender',
        'country',
        'city',
        'address',
        'date_of_birth',
        'bio',
        'price_per_session',
        'is_approved',
        'profile_image_url',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'languages' => 'array',
        'price_per_session' => 'decimal:2',
        'is_approved' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function availabilities()
    {
        return $this->hasMany(PsychologistAvailability::class, 'psychologist_id');
    }

    public function diplomas()
    {
        return $this->hasMany(PsychologistDiploma::class, 'psychologist_profile_id');
    }

    public function specialisations()
    {
        return $this->belongsToMany(
            Specialisation::class,
            'psychologist_profile_specialisation',
            'psychologist_profile_id',
            'specialisation_id'
        )->withTimestamps();
    }

    public function expertises()
    {
        return $this->belongsToMany(
            Expertise::class,
            'psychologist_profile_expertise',
            'psychologist_profile_id',
            'expertise_id'
        )->withTimestamps();
    }

    public function verificationDetails()
    {
        return $this->hasOne(PsychologistVerificationDetails::class);
    }

    private function resolveStoredFileUrl(?string $value)
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

    private function deleteStoredFile(?string $value)
    {
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

    public function getProfileImageUrlAttribute($value)
    {
        return $this->resolveStoredFileUrl($value);
    }

    public function getCvAttribute($value)
    {
        return $this->resolveStoredFileUrl($value);
    }

    public function deleteProfileImageFile()
    {
        return $this->deleteStoredFile($this->getAttributes()['profile_image_url'] ?? null);
    }

    public function deleteCvFile()
    {
        return $this->deleteStoredFile($this->getAttributes()['cv'] ?? null);
    }
}
