<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PsychologistAvailability extends Model
{
    use HasFactory;

    protected $fillable = [
        'psychologist_id',
        'day_of_week',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
    ];

    public function psychologistProfile()
    {
        return $this->belongsTo(PsychologistProfile::class, 'psychologist_id');
    }
}
