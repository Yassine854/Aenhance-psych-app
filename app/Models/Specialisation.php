<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Specialisation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function psychologistProfiles()
    {
        return $this->belongsToMany(
            PsychologistProfile::class,
            'psychologist_profile_specialisation',
            'specialisation_id',
            'psychologist_profile_id'
        )->withTimestamps();
    }
}
