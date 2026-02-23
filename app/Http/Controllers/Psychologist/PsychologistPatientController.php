<?php

namespace App\Http\Controllers\Psychologist;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PsychologistPatientController extends Controller
{
    public function index(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! method_exists($user, 'isPsychologist') || ! $user->isPsychologist()) {
            return redirect()->route('dashboard');
        }

        $searchField = strtolower(trim((string) $request->input('search_field', 'patient')));
        $searchQuery = trim((string) $request->input('search_query', ''));
        $searchDate = trim((string) $request->input('search_date', ''));

        if (! in_array($searchField, ['patient', 'date'], true)) {
            $searchField = 'patient';
        }

        $latestDurationSub = DB::table('appointment_sessions as s2')
            ->join('appointments as a2', 'a2.id', '=', 's2.appointment_id')
            ->select('s2.duration_minutes')
            ->whereColumn('a2.patient_id', 'patients.id')
            ->where('a2.psychologist_id', $user->id)
            ->orderByDesc('s2.started_at')
            ->limit(1);

        $patientsQuery = DB::table('appointments')
            ->join('users as patients', 'appointments.patient_id', '=', 'patients.id')
            ->leftJoin('patient_profiles', 'patients.id', '=', 'patient_profiles.user_id')
            ->join('appointment_sessions', 'appointment_sessions.appointment_id', '=', 'appointments.id')
            ->where('appointments.psychologist_id', $user->id)
            ->when($searchField === 'date' && $searchDate !== '', function ($q) use ($searchDate) {
                $q->whereDate('appointment_sessions.started_at', $searchDate);
            })
            ->when($searchField === 'patient' && $searchQuery !== '', function ($q) use ($searchQuery) {
                $q->where('patients.name', 'like', '%'.$searchQuery.'%');
            })
            ->select(
                'patients.id as patient_id',
                'patients.name as patient_name',
                'patient_profiles.profile_image_url as profile_image_url',
                'patient_profiles.date_of_birth as date_of_birth',
                DB::raw('MAX(appointment_sessions.started_at) as last_session_started_at')
            )
            ->selectSub($latestDurationSub, 'last_session_duration')
            ->groupBy(
                'patients.id',
                'patients.name',
                'patient_profiles.profile_image_url',
                'patient_profiles.date_of_birth'
            )
            ->orderBy('patients.name')
            ->orderByDesc('last_session_started_at');

        /** @var LengthAwarePaginator $patients */
        $patients = $patientsQuery
            ->paginate(15)
            ->appends($request->query());

        $mappedItems = collect($patients->items())->map(function ($r) {
            return [
                'id' => $r->patient_id,
                'name' => $r->patient_name,
                'profile_image_url' => $r->profile_image_url ?? null,
                'date_of_birth' => $r->date_of_birth ?? null,
                'age' => $r->date_of_birth ? Carbon::parse($r->date_of_birth)->age : null,
                'last_session_started_at' => $r->last_session_started_at,
                'last_session_duration' => $r->last_session_duration !== null ? (int) $r->last_session_duration : null,
            ];
        })->values()->all();

        $payload = new LengthAwarePaginator(
            $mappedItems,
            $patients->total(),
            $patients->perPage(),
            $patients->currentPage(),
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return Inertia::render('Psychologist/Patients/Index', [
            'patients' => $payload,
            'filters' => [
                'search_field' => $searchField,
                'search_query' => $searchQuery,
                'search_date' => $searchDate,
            ],
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
