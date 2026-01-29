<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\ActivityLogger;

class AppointmentSessionController extends Controller
{
    public function show(Request $request, Appointment $appointment)
    {
        $user = $request->user();
        if (! $user) {
            abort(403);
        }

        if (! $this->isParticipantOrAdmin($appointment, $user)) {
            abort(403);
        }

        $session = AppointmentSession::query()->where('appointment_id', $appointment->id)->first();

        return response()->json([
            'appointmentId' => $appointment->id,
            'session' => $session ? $this->sessionPayload($session) : null,
            'serverNow' => now()->toIso8601String(),
        ]);
    }

    public function join(Request $request, Appointment $appointment)
    {
        $user = $request->user();
        if (! $user) {
            abort(403);
        }

        if (! $this->isParticipantOrAdmin($appointment, $user)) {
            abort(403);
        }

        $validated = $request->validate([
            'roomId' => ['required', 'string'],
        ]);

        if (strtolower((string) $appointment->status) !== 'confirmed') {
            return response()->json(['message' => 'Appointment is not confirmed.'], 422);
        }

        $role = $this->roleForUser($appointment, $user);
        if (! $role && ! (method_exists($user, 'isAdmin') && $user->isAdmin())) {
            abort(403);
        }

        $prevStatus = AppointmentSession::query()->where('appointment_id', $appointment->id)->value('status');

        $session = DB::transaction(function () use ($appointment, $validated, $role) {
            /** @var AppointmentSession $session */
            $session = AppointmentSession::query()->lockForUpdate()->firstOrCreate(
                ['appointment_id' => $appointment->id],
                ['room_id' => (string) $validated['roomId'], 'status' => 'active']
            );

            // Keep room_id stable.
            if (! $session->room_id) {
                $session->room_id = (string) $validated['roomId'];
            }

            // Mark participant presence.
            $now = now();
            if ($role === 'patient') {
                if (! $session->patient_joined_at) {
                    $session->patient_joined_at = $now;
                }
                $session->patient_in_room = true;
                $session->patient_left_at = null;
            } elseif ($role === 'psychologist') {
                if (! $session->psychologist_joined_at) {
                    $session->psychologist_joined_at = $now;
                }
                $session->psychologist_in_room = true;
                $session->psychologist_left_at = null;
            }

            // Start the session only when both have joined at least once.
            if (! $session->started_at && $session->patient_joined_at && $session->psychologist_joined_at) {
                $session->started_at = $now;
            }

            $session->status = (string) ($session->status ?: 'active');
            $session->save();

            return $session->fresh();
        });

        $newStatus = $session->status ?? null;
        if ((string) $prevStatus !== (string) $newStatus) {
            ActivityLogger::log($user->id ?? null, $user?->role ?? null, 'updated_session_status', 'AppointmentSession', $session->id ?? null, 'Session status changed from '.($prevStatus ?? 'null').' to '.($newStatus ?? 'null'));
        }

        return response()->json([
            'appointmentId' => $appointment->id,
            'session' => $this->sessionPayload($session),
            'serverNow' => now()->toIso8601String(),
        ]);
    }

    public function leave(Request $request, Appointment $appointment)
    {
        $user = $request->user();
        if (! $user) {
            abort(403);
        }

        if (! $this->isParticipantOrAdmin($appointment, $user)) {
            abort(403);
        }

        $role = $this->roleForUser($appointment, $user);
        if (! $role && ! (method_exists($user, 'isAdmin') && $user->isAdmin())) {
            abort(403);
        }

        $session = DB::transaction(function () use ($appointment, $role) {
            /** @var AppointmentSession|null $session */
            $session = AppointmentSession::query()->lockForUpdate()->where('appointment_id', $appointment->id)->first();
            if (! $session) {
                return null;
            }

            $now = now();
            if ($role === 'patient') {
                $session->patient_in_room = false;
                $session->patient_left_at = $now;
            } elseif ($role === 'psychologist') {
                $session->psychologist_in_room = false;
                $session->psychologist_left_at = $now;
            }

            $session->save();

            return $session->fresh();
        });

        return response()->json([
            'appointmentId' => $appointment->id,
            'session' => $session ? $this->sessionPayload($session) : null,
            'serverNow' => now()->toIso8601String(),
        ]);
    }

    public function end(Request $request, Appointment $appointment)
    {
        $user = $request->user();
        if (! $user) {
            abort(403);
        }

        $isPsychologist = method_exists($user, 'isPsychologist') && $user->isPsychologist();
        $isAdmin = method_exists($user, 'isAdmin') && $user->isAdmin();

        if (! $isPsychologist && ! $isAdmin) {
            abort(403);
        }

        if ((int) $appointment->psychologist_id !== (int) $user->id && ! $isAdmin) {
            abort(403);
        }

        $prevStatus = AppointmentSession::query()->where('appointment_id', $appointment->id)->value('status');

        $session = DB::transaction(function () use ($appointment) {
            /** @var AppointmentSession $session */
            $session = AppointmentSession::query()->lockForUpdate()->firstOrCreate(
                ['appointment_id' => $appointment->id],
                ['room_id' => (string) $appointment->session?->room_id, 'status' => 'active']
            );

            $now = now();
            if (! $session->started_at) {
                // If the psychologist ends before both joined, treat as a zero-length session.
                $session->started_at = $now;
            }

            $session->ended_at = $now;
            $session->duration_minutes = max(0, (int) $session->started_at->diffInMinutes($now));
            $session->status = 'completed';

            $session->patient_in_room = false;
            $session->psychologist_in_room = false;

            $session->save();

            // Completing the session completes the appointment.
            $appointment = Appointment::query()->lockForUpdate()->find($appointment->id);
            if ($appointment && strtolower((string) $appointment->status) === 'confirmed') {
                $appointment->status = 'completed';
                $appointment->save();
            }

            return $session->fresh();
        });

        $newStatus = $session->status ?? null;
        if ((string) $prevStatus !== (string) $newStatus) {
            ActivityLogger::log($user->id ?? null, $user?->role ?? null, 'updated_session_status', 'AppointmentSession', $session->id ?? null, 'Session status changed from '.($prevStatus ?? 'null').' to '.($newStatus ?? 'null'));
        }

        return response()->json([
            'appointmentId' => $appointment->id,
            'session' => $this->sessionPayload($session),
            'serverNow' => now()->toIso8601String(),
        ]);
    }

    private function sessionPayload(AppointmentSession $session): array
    {
        return [
            'id' => $session->id,
            'roomId' => (string) $session->room_id,
            'status' => (string) $session->status,
            'started_at' => optional($session->started_at)->toIso8601String(),
            'ended_at' => optional($session->ended_at)->toIso8601String(),
            'duration_minutes' => $session->duration_minutes,
            'patient_in_room' => (bool) $session->patient_in_room,
            'psychologist_in_room' => (bool) $session->psychologist_in_room,
            'patient_joined_at' => optional($session->patient_joined_at)->toIso8601String(),
            'psychologist_joined_at' => optional($session->psychologist_joined_at)->toIso8601String(),
            'patient_left_at' => optional($session->patient_left_at)->toIso8601String(),
            'psychologist_left_at' => optional($session->psychologist_left_at)->toIso8601String(),
        ];
    }

    private function isParticipantOrAdmin(Appointment $appointment, $user): bool
    {
        $isParticipant = ((int) $appointment->patient_id === (int) $user->id) || ((int) $appointment->psychologist_id === (int) $user->id);
        $isAdmin = method_exists($user, 'isAdmin') && $user->isAdmin();

        return $isParticipant || $isAdmin;
    }

    private function roleForUser(Appointment $appointment, $user): ?string
    {
        if ((int) $appointment->patient_id === (int) $user->id) return 'patient';
        if ((int) $appointment->psychologist_id === (int) $user->id) return 'psychologist';
        return null;
    }
}
