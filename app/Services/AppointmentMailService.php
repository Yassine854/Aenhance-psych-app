<?php

namespace App\Services;

use App\Mail\AppointmentConfirmedMail;
use App\Models\Appointment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\AppointmentCancelledMail;

class AppointmentMailService
{
    public static function sendConfirmed(Appointment $appointment): void
    {
        $appointment->loadMissing([
            'patient:id,name,email',
            'psychologist:id,name,email',
        ]);

        $patient = $appointment->patient;
        $psychologist = $appointment->psychologist;

        if ($patient && filter_var($patient->email, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::to($patient->email)->send(new AppointmentConfirmedMail($appointment, $patient, $psychologist ?? $patient));
            } catch (\Throwable $e) {
                Log::warning('Failed sending appointment confirmation email to patient', [
                    'appointment_id' => $appointment->id,
                    'patient_id' => $patient->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($psychologist && filter_var($psychologist->email, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::to($psychologist->email)->send(new AppointmentConfirmedMail($appointment, $psychologist, $patient ?? $psychologist));
            } catch (\Throwable $e) {
                Log::warning('Failed sending appointment confirmation email to psychologist', [
                    'appointment_id' => $appointment->id,
                    'psychologist_id' => $psychologist->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public static function sendCancelled(Appointment $appointment): void
    {
        $appointment->loadMissing([
            'patient:id,name,email',
            'psychologist:id,name,email',
        ]);

        $patient = $appointment->patient;
        $psychologist = $appointment->psychologist;

        if ($patient && filter_var($patient->email, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::to($patient->email)->send(new AppointmentCancelledMail($appointment, $patient, $psychologist ?? $patient));
            } catch (\Throwable $e) {
                Log::warning('Failed sending appointment cancelled email to patient', [
                    'appointment_id' => $appointment->id,
                    'patient_id' => $patient->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($psychologist && filter_var($psychologist->email, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::to($psychologist->email)->send(new AppointmentCancelledMail($appointment, $psychologist, $patient ?? $psychologist));
            } catch (\Throwable $e) {
                Log::warning('Failed sending appointment cancelled email to psychologist', [
                    'appointment_id' => $appointment->id,
                    'psychologist_id' => $psychologist->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
