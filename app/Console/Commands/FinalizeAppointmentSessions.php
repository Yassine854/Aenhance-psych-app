<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\AppointmentSession;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\ActivityLogger;

class FinalizeAppointmentSessions extends Command
{
    protected $signature = 'sessions:finalize';

    protected $description = 'Auto-complete appointment sessions and mark no-shows.';

    public function handle(): int
    {
        $now = now();

        $this->finalizeNoShows($now);
        $this->finalizeCompletedByTimeout($now);

        return self::SUCCESS;
    }

    private function finalizeNoShows($now): void
    {
        // If 20 minutes have passed since scheduled_start and the session never started,
        // mark the appointment as no_show and the session as completed.
        $threshold = $now->copy()->subMinutes(20);

        $appointments = Appointment::query()
            ->where('status', 'confirmed')
            ->where('scheduled_start', '<=', $threshold)
            ->with('session')
            ->limit(500)
            ->get();

        foreach ($appointments as $appointment) {
            DB::transaction(function () use ($appointment, $now) {
                $appointment = Appointment::query()->lockForUpdate()->find($appointment->id);
                if (! $appointment || strtolower((string) $appointment->status) !== 'confirmed') {
                    return;
                }

                /** @var AppointmentSession $session */
                $session = AppointmentSession::query()->lockForUpdate()->firstOrCreate(
                    ['appointment_id' => $appointment->id],
                    ['room_id' => (string) Str::uuid(), 'status' => 'active']
                );

                // Ensure we never end up with an empty/invalid room_id.
                if (! $session->room_id) {
                    $session->room_id = (string) Str::uuid();
                    $session->save();
                }

                // If the session already ended, don't touch it.
                if ($session->ended_at || strtolower((string) $session->status) === 'completed') {
                    return;
                }

                // If the session already started, it's not a no-show.
                if ($session->started_at) {
                    return;
                }

                $patientJoined = (bool) $session->patient_joined_at;
                $psychJoined = (bool) $session->psychologist_joined_at;

                $noShowBy = null;
                $noShowUserId = null;

                if ($patientJoined && ! $psychJoined) {
                    $noShowBy = 'psychologist';
                    $noShowUserId = (int) $appointment->psychologist_id;
                } elseif ($psychJoined && ! $patientJoined) {
                    $noShowBy = 'patient';
                    $noShowUserId = (int) $appointment->patient_id;
                } elseif (! $patientJoined && ! $psychJoined) {
                    // No one joined; still mark no_show but leave identifiers null.
                    $noShowBy = null;
                    $noShowUserId = null;
                } else {
                    // Both joined at some point (rare here), so not a no-show.
                    return;
                }

                $appointment->status = 'no_show';
                $appointment->no_show_by = $noShowBy;
                $appointment->no_show_user_id = $noShowUserId;
                $appointment->save();

                // Create an activity log for the no-show event, mentioning who missed.
                try {
                    $who = 'Both participants';
                    if ($noShowBy === 'patient') {
                        $who = 'Patient did not show';
                    } elseif ($noShowBy === 'psychologist') {
                        $who = 'Psychologist did not show';
                    } elseif ($noShowBy === null) {
                        // explicit clarity
                        $who = 'No one joined (both did not show)';
                    }

                    $desc = 'Appointment marked no-show: '.$who;

                    ActivityLogger::log(
                        $noShowUserId ?? null,
                        $noShowBy ? strtoupper($noShowBy) : 'SYSTEM',
                        'no_show_appointment',
                        'Appointment',
                        $appointment->id,
                        $desc
                    );
                } catch (\Throwable $e) {
                    // Don't fail the job if logging fails; just continue.
                }

                $session->status = 'completed';
                $session->ended_at = $now;
                $session->duration_minutes = 0;
                $session->patient_in_room = false;
                $session->psychologist_in_room = false;
                $session->save();
            });
        }
    }

    private function finalizeCompletedByTimeout($now): void
    {
        // If session has been running for >= 60 minutes and both have left the room,
        // auto-complete it (psychologist may have forgotten to click "End session").
        $threshold = $now->copy()->subMinutes(60);

        $sessions = AppointmentSession::query()
            ->where('status', 'active')
            ->whereNotNull('started_at')
            ->whereNull('ended_at')
            ->where('started_at', '<=', $threshold)
            ->where('patient_in_room', false)
            ->where('psychologist_in_room', false)
            ->with('appointment')
            ->limit(500)
            ->get();

        foreach ($sessions as $session) {
            DB::transaction(function () use ($session, $now) {
                $session = AppointmentSession::query()->lockForUpdate()->find($session->id);
                if (! $session || $session->ended_at || strtolower((string) $session->status) !== 'active') {
                    return;
                }

                if (! $session->started_at) {
                    return;
                }

                if ((bool) $session->patient_in_room || (bool) $session->psychologist_in_room) {
                    return;
                }

                $session->ended_at = $now;
                $session->duration_minutes = max(0, (int) $session->started_at->diffInMinutes($now));
                $session->status = 'completed';
                $session->save();

                $appointment = Appointment::query()->lockForUpdate()->find($session->appointment_id);
                if ($appointment && strtolower((string) $appointment->status) === 'confirmed') {
                    $appointment->status = 'completed';
                    $appointment->save();
                    // Log system-completed appointment
                    try {
                        ActivityLogger::log(
                            null,
                            'SYSTEM',
                            'completed_appointment',
                            'Appointment',
                            $appointment->id,
                            'Appointment auto-completed by system'
                        );
                    } catch (\Throwable $e) {
                        // ignore logging errors
                    }
                }
            });
        }
    }
}
