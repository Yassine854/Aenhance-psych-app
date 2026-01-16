<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
