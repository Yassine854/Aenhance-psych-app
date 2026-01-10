<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentSession extends Model
{
    use HasFactory;

    protected $table = 'appointment_sessions';

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'patient_joined_at' => 'datetime',
        'psychologist_joined_at' => 'datetime',
        'patient_left_at' => 'datetime',
        'psychologist_left_at' => 'datetime',
        'patient_in_room' => 'boolean',
        'psychologist_in_room' => 'boolean',
    ];

    protected $fillable = [
        'appointment_id',
        'room_id',
        'patient_joined_at',
        'psychologist_joined_at',
        'patient_left_at',
        'psychologist_left_at',
        'patient_in_room',
        'psychologist_in_room',
        'started_at',
        'ended_at',
        'duration_minutes',
        'status',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
