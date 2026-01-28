<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PsychologistPayout extends Model
{
    use HasFactory;

    protected $table = 'psychologist_payouts';

    protected $fillable = [
        'payment_id',
        'psychologist_id',
        'appointment_id',
        'gross_amount',
        'platform_fee',
        'net_amount',
        'currency',
        'status',
        'estimated_availability',
        'paid_at',
        'refund_at',
    ];

    protected $casts = [
        'gross_amount' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'estimated_availability' => 'datetime',
        'paid_at' => 'datetime',
        'refund_at' => 'datetime',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function psychologist()
    {
        return $this->belongsTo(User::class, 'psychologist_id');
    }
}
