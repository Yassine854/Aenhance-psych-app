<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Http\Requests\StoreAppointmentSessionNoteRequest;
use App\Http\Requests\UpdateAppointmentSessionNoteRequest;
use Illuminate\Http\JsonResponse;
use App\Services\ActivityLogger;

class AppointmentSessionNoteController extends Controller
{
    public function store(StoreAppointmentSessionNoteRequest $request)
    {
        $user = $request->user();

        // Only psychologists may create session notes
        if (! method_exists($user, 'isPsychologist') || ! $user->isPsychologist()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $session = DB::table('appointment_sessions')->where('id', $request->input('appointment_session_id'))->first();
        if (! $session) {
            return response()->json(['message' => 'Session not found'], 422);
        }

        // Ensure the session belongs to an appointment and fetch appointment
        $appointment = DB::table('appointments')->where('id', $session->appointment_id)->first();
        if (! $appointment) {
            return response()->json(['message' => 'Appointment not found for session'], 422);
        }

        // Ensure the psychologist is the owner of the appointment
        if ((int) $appointment->psychologist_id !== (int) $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Appointment must have a patient_id
        if (empty($appointment->patient_id)) {
            return response()->json(['message' => 'Appointment is missing patient information'], 422);
        }

        try {
            $now = Carbon::now();

            // Default session_date and session_duration from the session record
            $sessionDate = $request->input('session_date');
            if (empty($sessionDate) && ! empty($session->started_at)) {
                $sessionDate = (new Carbon($session->started_at))->toDateTimeString();
            } elseif (! empty($sessionDate)) {
                try {
                    $sessionDate = (new Carbon($sessionDate))->toDateTimeString();
                } catch (\Exception $e) {
                    // leave as-is; DB will validate
                }
            }

            $sessionDuration = $request->input('session_duration');
            if (empty($sessionDuration) && isset($session->duration_minutes)) {
                $sessionDuration = (int) $session->duration_minutes;
            } else {
                $sessionDuration = (int) $sessionDuration;
            }

            $id = DB::table('appointment_session_notes')->insertGetId([
                'appointment_session_id' => $session->id,
                'psychologist_id' => $user->id,
                'patient_id' => $appointment->patient_id,
                'session_date' => $sessionDate,
                'session_duration' => (int) $sessionDuration,
                'session_mode' => (string) ($request->input('session_mode') ?: 'video_audio'),
                'subjective' => $request->input('subjective') ?: null,
                'objective' => $request->input('objective') ?: null,
                'assessment' => $request->input('assessment') ?: null,
                'intervention' => $request->input('intervention') ?: null,
                'risk_level' => (string) ($request->input('risk_level') ?: 'none'),
                'plan' => $request->input('plan') ?: null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $record = DB::table('appointment_session_notes')->where('id', $id)->first();
            ActivityLogger::log($user->id ?? null, $user?->role ?? null, 'created_session_note', 'AppointmentSessionNote', $id, 'Created session note for session '.$session->id.' appointment '.$appointment->id);
            return response()->json($record, 201);
        } catch (\Exception $e) {
            logger()->error('Failed storing session note: '.$e->getMessage());
            return response()->json(['message' => 'Could not store session note'], 500);
        }
    }

    // Return the session note for the latest session for a given appointment
    public function showByAppointment($appointmentId, Request $request): JsonResponse
    {
        $user = $request->user();

        $session = DB::table('appointment_sessions')->where('appointment_id', $appointmentId)->orderByDesc('started_at')->first();
        if (! $session) {
            return response()->json(['message' => 'Session not found'], 404);
        }
        $note = DB::table('appointment_session_notes')->where('appointment_session_id', $session->id)->first();

        // Verify ownership: either the appointment patient or psychologist
        $appointment = DB::table('appointments')->where('id', $appointmentId)->first();
        if (! $appointment) return response()->json(['message' => 'Appointment not found'], 404);

        $isAllowed = false;
        if ($user) {
            if ((int) $user->id === (int) $appointment->patient_id) $isAllowed = true;
            if ((int) $user->id === (int) $appointment->psychologist_id) $isAllowed = true;
        }

        if (! $isAllowed) return response()->json(['message' => 'Forbidden'], 403);

        // Return session (always) and note (may be null) so frontend can populate/create notes
        return response()->json(['session' => $session, 'note' => $note]);
    }

    // Update a session note
    public function update($noteId, UpdateAppointmentSessionNoteRequest $request): JsonResponse
    {
        $user = $request->user();

        $note = DB::table('appointment_session_notes')->where('id', $noteId)->first();
        if (! $note) return response()->json(['message' => 'Note not found'], 404);

        $session = DB::table('appointment_sessions')->where('id', $note->appointment_session_id)->first();
        if (! $session) return response()->json(['message' => 'Session not found'], 404);

        $appointment = DB::table('appointments')->where('id', $session->appointment_id)->first();
        if (! $appointment) return response()->json(['message' => 'Appointment not found'], 404);

        // verify ownership
        $isAllowed = false;
        if ($user) {
            if ((int) $user->id === (int) $appointment->patient_id) $isAllowed = true;
            if ((int) $user->id === (int) $appointment->psychologist_id) $isAllowed = true;
        }
        if (! $isAllowed) return response()->json(['message' => 'Forbidden'], 403);

        try {
            $now = Carbon::now();

            $sessionDate = $request->input('session_date') ?: $note->session_date;
            try { $sessionDate = (new Carbon($sessionDate))->toDateTimeString(); } catch (\Exception $e) {}
            $sessionDuration = $request->input('session_duration') !== null ? (int) $request->input('session_duration') : (int) $note->session_duration;

            DB::table('appointment_session_notes')->where('id', $noteId)->update([
                'session_date' => $sessionDate,
                'session_duration' => $sessionDuration,
                'session_mode' => (string) ($request->input('session_mode') ?: $note->session_mode),
                'risk_level' => (string) ($request->input('risk_level') ?: $note->risk_level),
                'subjective' => $request->input('subjective') ?: null,
                'objective' => $request->input('objective') ?: null,
                'assessment' => $request->input('assessment') ?: null,
                'intervention' => $request->input('intervention') ?: null,
                'plan' => $request->input('plan') ?: null,
                'updated_at' => $now,
            ]);

            $updated = DB::table('appointment_session_notes')->where('id', $noteId)->first();
            ActivityLogger::log($user->id ?? null, $user?->role ?? null, 'updated_session_note', 'AppointmentSessionNote', $noteId, 'Updated session note '.$noteId.' for appointment '.$appointment->id);
            return response()->json($updated);
        } catch (\Exception $e) {
            logger()->error('Failed updating session note: '.$e->getMessage());
            return response()->json(['message' => 'Could not update session note'], 500);
        }
    }
}
