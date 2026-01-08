<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $casts = [
        'scheduled_start' => 'datetime',
        'scheduled_end' => 'datetime',
        'canceled_at' => 'datetime',
    ];

    protected $fillable = [
        'patient_id',
        'psychologist_id',
        'scheduled_start',
        'scheduled_end',
        'status',
        'canceled_by',
        'canceled_by_user_id',
        'cancellation_reason',
        'canceled_at',
        'price',
        'currency',
    ];

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function psychologist()
    {
        return $this->belongsTo(User::class, 'psychologist_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}

