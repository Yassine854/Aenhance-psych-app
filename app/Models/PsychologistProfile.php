<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
