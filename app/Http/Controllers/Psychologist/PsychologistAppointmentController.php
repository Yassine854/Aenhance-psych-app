<?php

namespace App\Http\Controllers\Psychologist;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use App\Services\ActivityLogger;

class PsychologistAppointmentController extends Controller
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

        /** @var LengthAwarePaginator $appointments */
        $appointmentsQuery = Appointment::query()
            ->where('psychologist_id', $user->id)
            ->with([
                'patient:id,name,role',
                'session:id,appointment_id,room_id,status,started_at',
            ]);

        if ($searchField === 'date') {
            if ($searchDate !== '') {
                $appointmentsQuery->whereDate('scheduled_start', $searchDate);
            }
        } elseif ($searchQuery !== '') {
            $appointmentsQuery->whereHas('patient', function ($q) use ($searchQuery) {
                $q->where('name', 'like', '%'.$searchQuery.'%');
            });
        }

        $appointments = $appointmentsQuery
            ->orderByDesc('scheduled_start')
            ->paginate(15)
            ->appends($request->query());

        $mappedItems = collect($appointments->items())->map(function (Appointment $a) {
            $start = $a->scheduled_start;
            $cutoff = now()->addHours(24);
            $canCancel = $start ? $start->greaterThanOrEqualTo($cutoff) : false;

            return [
                'id' => $a->id,
                'patient' => $a->patient ? [
                    'id' => $a->patient->id,
                    'name' => $a->patient->name,
                ] : null,
                'scheduled_start' => optional($a->scheduled_start)->toISOString() ?? (string) $a->scheduled_start,
                'scheduled_end' => optional($a->scheduled_end)->toISOString() ?? (string) $a->scheduled_end,
                'status' => (string) $a->status,

                'canceled_by' => $a->canceled_by,
                'canceled_by_user_id' => $a->canceled_by_user_id,
                'cancellation_reason' => $a->cancellation_reason,
                'canceled_at' => optional($a->canceled_at)->toISOString() ?? ($a->canceled_at ? (string) $a->canceled_at : null),

                'can_cancel' => $canCancel,
                'session_room_id' => (string) ($a->session?->room_id ?: ''),
                'session_status' => (string) ($a->session?->status ?: ''),
                'session_started_at' => optional($a->session?->started_at)->toISOString() ?? ($a->session?->started_at ? (string) $a->session->started_at : null),
            ];
        })->values()->all();

        $payload = new LengthAwarePaginator(
            $mappedItems,
            $appointments->total(),
            $appointments->perPage(),
            $appointments->currentPage(),
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return Inertia::render('Psychologist/Appointments/Index', [
            'appointments' => $payload,
            'status' => session('status'),
            'filters' => [
                'search_field' => $searchField,
                'search_query' => $searchQuery,
                'search_date' => $searchDate,
            ],
        ]);
    }

    public function cancel(Request $request, Appointment $appointment): RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! method_exists($user, 'isPsychologist') || ! $user->isPsychologist()) {
            abort(403);
        }

        if ((int) $appointment->psychologist_id !== (int) $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'cancellation_reason' => ['required', 'string', 'max:255'],
        ]);

        $status = strtolower(trim((string) $appointment->status));
        if (in_array($status, ['cancelled', 'completed', 'no_show'], true)) {
            return redirect()->back()->with('error', 'This appointment can no longer be cancelled.');
        }

        $start = $appointment->scheduled_start;
        if (! $start) {
            return redirect()->back()->with('error', 'Missing appointment start time.');
        }

        $cutoff = now()->addHours(24);
        if ($start->lt($cutoff)) {
            return redirect()->back()->with('error', 'You can only cancel at least 24 hours before the appointment.');
        }

        // Only allow cancelling from common active states.
        if (! in_array($status, ['pending', 'confirmed'], true)) {
            throw ValidationException::withMessages([
                'status' => 'This appointment cannot be cancelled from its current state.',
            ]);
        }

        $appointment->update([
            'status' => 'cancelled',
            'canceled_by' => 'psychologist',
            'canceled_by_user_id' => $user->id,
            'cancellation_reason' => $validated['cancellation_reason'],
            'canceled_at' => now(),
        ]);

        ActivityLogger::log($user->id, $user->role ?? null, 'cancelled_appointment', 'Appointment', $appointment->id, 'Psychologist cancelled appointment: '.$validated['cancellation_reason']);

        return redirect()->back()->with('status', 'Appointment cancelled.');
    }
}
