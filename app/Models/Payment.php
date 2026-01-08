<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    protected $fillable = [
        'appointment_id',
        'amount',
        'currency',
        'transaction_id',
        'provider',
        'status',
        'payment_method',
        'failure_reason',
        'refund_reason',
        'paid_at',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
