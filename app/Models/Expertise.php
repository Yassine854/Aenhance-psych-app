<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expertise extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function psychologistProfiles()
    {
        return $this->belongsToMany(
            PsychologistProfile::class,
            'psychologist_profile_expertise',
            'expertise_id',
            'psychologist_profile_id'
        )->withTimestamps();
    }
}
