<?php

namespace App\Http\Controllers\Psychologist;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Inertia\Inertia;

class PsychologistPatientController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (! $user || ! method_exists($user, 'isPsychologist') || ! $user->isPsychologist()) {
            return redirect()->route('dashboard');
        }

        $search = trim((string) $request->input('search', ''));

        $rows = DB::table('appointments')
            ->join('users as patients', 'appointments.patient_id', '=', 'patients.id')
            ->leftJoin('patient_profiles', 'patients.id', '=', 'patient_profiles.user_id')
            ->join('appointment_sessions', 'appointment_sessions.appointment_id', '=', 'appointments.id')
            ->leftJoin('appointment_session_notes', 'appointment_session_notes.appointment_session_id', '=', 'appointment_sessions.id')
            ->where('appointments.psychologist_id', $user->id)
            ->when($search !== '', function ($q) use ($search) {
                $q->where('patients.name', 'like', "%{$search}%");
            })
            ->select(
                'patients.id as patient_id',
                'patients.name as patient_name',
                'patient_profiles.profile_image_url as profile_image_url',
                'patient_profiles.date_of_birth as date_of_birth',
                'appointment_sessions.id as session_id',
                'appointment_sessions.started_at as session_started_at',
                'appointment_sessions.duration_minutes as session_duration',
                'appointment_session_notes.id as note_id'
            )
            ->orderBy('patients.name')
            ->orderByDesc('appointment_sessions.started_at')
            ->get();

        $grouped = [];
        foreach ($rows as $r) {
            $pid = $r->patient_id;
            if (! isset($grouped[$pid])) {
                $grouped[$pid] = [
                    'id' => $pid,
                    'name' => $r->patient_name,
                    'profile_image_url' => $r->profile_image_url ?? null,
                    'date_of_birth' => $r->date_of_birth ?? null,
                    'age' => $r->date_of_birth ? Carbon::parse($r->date_of_birth)->age : null,
                    'profile_cloudinary' => $r->profile_cloudinary ?? null,
                    'sessions' => [],
                ];
            }

            $grouped[$pid]['sessions'][] = [
                'session_id' => $r->session_id,
                'started_at' => $r->session_started_at,
                'duration' => $r->session_duration,
                'note_id' => $r->note_id,
            ];
        }

        $patients = array_values($grouped);

        return Inertia::render('Psychologist/Patients/Index', [
            'patients' => $patients,
            'search' => $search,
        ]);
    }

    // Return notes for a patient (filtered by optional start_date/end_date)
    public function notes(Request $request, $patientId)
    {
        $user = $request->user();
        if (! $user || ! method_exists($user, 'isPsychologist') || ! $user->isPsychologist()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // ensure the psychologist has at least one appointment with this patient
        $has = DB::table('appointments')
            ->where('psychologist_id', $user->id)
            ->where('patient_id', $patientId)
            ->exists();

        if (! $has) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $start = $request->input('start_date');
        $end = $request->input('end_date');

        $q = DB::table('appointment_session_notes')
            ->where('patient_id', $patientId)
            ->where('psychologist_id', $user->id);

        if ($start) {
            $q->whereDate('session_date', '>=', $start);
        }
        if ($end) {
            $q->whereDate('session_date', '<=', $end);
        }

        $notes = $q->orderByDesc('session_date')->get();

        return response()->json($notes);
    }
}
