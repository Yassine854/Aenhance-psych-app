<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PsychologistPayout;
use App\Models\Payment;
use App\Models\AppFee;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class Appointment extends Model
{
    public function session()
    {
        return $this->hasOne(AppointmentSession::class);
    }

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
        'no_show_by',
        'no_show_user_id',
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

    protected static function booted()
    {
        static::saved(function (Appointment $appointment) {
            try {
                $original = $appointment->getOriginal('status');
                $current = $appointment->status;

                // Only act when appointment status flips to 'completed'
                if ($current !== 'completed' || $original === 'completed') {
                    return;
                }

                // find paid payments for this appointment
                $paidPayments = Payment::where('appointment_id', $appointment->id)
                    ->where('status', 'paid')
                    ->get();

                foreach ($paidPayments as $payment) {
                    // skip if payout already exists for this payment
                    $exists = PsychologistPayout::where('payment_id', $payment->id)->exists();
                    if ($exists) continue;

                    $gross = (float) $payment->amount;
                    $platformFee = 0.0;
                    try {
                        $appFee = AppFee::first();
                        if ($appFee && $appFee->percentage) {
                            $platformFee = round($gross * (float)$appFee->percentage / 100.0, 2);
                        }
                    } catch (\Throwable $e) {
                        Log::warning('Unable to determine app fee in Appointment hook: ' . $e->getMessage());
                    }

                    $net = round($gross - $platformFee, 2);

                    PsychologistPayout::create([
                        'payment_id' => $payment->id,
                        'psychologist_id' => $appointment->psychologist_id,
                        'appointment_id' => $appointment->id,
                        'gross_amount' => $gross,
                        'platform_fee' => $platformFee,
                        'net_amount' => $net,
                        'currency' => $payment->currency ?? 'TND',
                        'status' => 'pending',
                        'estimated_availability' => Carbon::now()->addDays(14),
                        'paid_at' => null,
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('Failed creating PsychologistPayout in Appointment hook: ' . $e->getMessage());
            }
        });
    }
}

