<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionRating extends Model
{
    use HasFactory;

    protected $table = 'session_ratings';

    protected $casts = [
        'rating' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $fillable = [
        'session_id',
        'patient_id',
        'psychologist_id',
        'rating',
        'comment',
    ];

    public function session()
    {
        return $this->belongsTo(AppointmentSession::class, 'session_id');
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function psychologist()
    {
        return $this->belongsTo(User::class, 'psychologist_id');
    }
}
