<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentBeneficiary extends Model
{
    protected $fillable = [
        'appointment_id',
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'relationship_to_patient',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}