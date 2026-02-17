<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Schema;
use App\Models\Log;
use App\Models\PsychologistProfile;
use App\Models\PatientProfile;
use App\Models\User;
use App\Models\AppointmentSession;

class LogsController extends Controller
{
    public function appointmentsIndex(Request $request)
    {
        $query = Log::query()
            ->with([
                'appointment.patient.patientProfile',
                'appointment.psychologist.psychologistProfile',
            ])
            ->where('target_type', 'Appointment')
            // hide internal laravel-registered logs to keep listings lightweight
            ->whereRaw("COALESCE(LOWER(actor_role), '') != ?", ['laravellogs'])
            ->orderBy('id', 'desc');

        // server-driven search: support search_field + search_query
        $searchField = strtolower(trim((string) $request->input('search_field', 'id')));
        $searchQuery = trim((string) $request->input('search_query', ''));

        // additional server-side filters: statuses, created_from/to, actor_role
        $rawStatuses = $request->input('statuses', []);
        if (is_string($rawStatuses)) {
            $rawStatuses = array_filter(array_map('trim', explode(',', $rawStatuses)));
        }
        $allowedStatuses = ['pending', 'confirmed', 'completed', 'cancelled', 'no_show'];
        $statuses = collect(is_array($rawStatuses) ? $rawStatuses : [])
            ->map(fn($v) => strtolower(trim((string)$v)))
            ->filter(fn($v) => in_array($v, $allowedStatuses, true))
            ->values()
            ->all();

        $createdFrom = trim((string) $request->input('created_from', ''));
        $createdTo = trim((string) $request->input('created_to', ''));
        $actorRoleFilter = trim((string) $request->input('actor_role', ''));

        if ($searchQuery !== '') {
            $q = $searchQuery;
            switch ($searchField) {
                case 'id':
                    // Autocomplete-style ID search: match any id containing the typed digits.
                    // Extract digits (so "#123" -> "123") and perform a LIKE match to allow partial matches like "1" -> 1,11,41.
                    $digits = preg_replace('/\D/', '', $q);
                    if ($digits !== '') {
                        $query->where('id', 'like', "%{$digits}%");
                    } else {
                        // fallback: non-digit input, attempt a LIKE on id as a string
                        $query->where('id', 'like', "%{$q}%");
                    }
                    break;
                case 'actor':
                    // Only search actor when logs.actor_id matches a user record.
                    $query->leftJoin('users', 'logs.actor_id', '=', 'users.id');
                    if (ctype_digit($q)) {
                        $query->where('logs.actor_id', $q);
                    } else {
                        $query->where(function ($s) use ($q) {
                            $s->where('users.name', 'like', "%{$q}%")
                              ->orWhere('users.email', 'like', "%{$q}%");
                            if (Schema::hasColumn('users', 'username')) {
                                $s->orWhere('users.username', 'like', "%{$q}%");
                            }
                        });
                    }
                    // ensure we still select log columns for pagination/models
                    $query->select('logs.*');
                    break;
                case 'status':
                                        $statusToken = strtolower(trim((string) $q));
                                        $query->where(function ($s) use ($q, $statusToken) {
                                                if (in_array($statusToken, ['active', 'created', 'in_room', 'in-room', 'in room'], true)) {
                                                        $s->orWhere('action', 'created_session_status')
                                                            ->orWhere('description', 'like', '%active%')
                                                            ->orWhere('description', 'like', '%in room%')
                                                            ->orWhere('description', 'like', '%in-room%');
                                                }

                                                if (in_array($statusToken, ['completed', 'complete', 'updated'], true)) {
                                                        $s->orWhere('action', 'updated_session_status')
                                                            ->orWhere('description', 'like', '%completed%')
                                                            ->orWhere('description', 'like', '%complete%');
                                                }

                                                $s->orWhere('action', 'like', "%{$q}%")
                                                    ->orWhere('description', 'like', "%{$q}%");
                                        });
                    break;
                case 'action':
                    $query->where('action', 'like', "%{$q}%");
                    break;
                default:
                    $query->where(function ($s) use ($q) {
                        $s->where('action', 'like', "%{$q}%")
                          ->orWhere('users.email', 'like', "%{$q}%")
                          ->orWhere('users.name', 'like', "%{$q}%");
                        if (Schema::hasColumn('users', 'username')) {
                            $s->orWhere('users.username', 'like', "%{$q}%");
                        }
                    });
            }
        }

        // apply status filters (map to action/description tokens)
        if (!empty($statuses)) {
            $query->where(function ($q) use ($statuses) {
                foreach ($statuses as $s) {
                    switch ($s) {
                        case 'pending':
                            $q->orWhere('action', 'like', '%created%')
                              ->orWhere('description', 'like', '%pending%');
                            break;
                        case 'confirmed':
                            $q->orWhere('action', 'like', '%confirm%')
                              ->orWhere('description', 'like', '%confirm%');
                            break;
                        case 'completed':
                            $q->orWhere('action', 'like', '%complete%')
                              ->orWhere('description', 'like', '%complete%');
                            break;
                        case 'cancelled':
                            $q->orWhere('action', 'like', '%cancel%')
                              ->orWhere('description', 'like', '%cancel%');
                            break;
                        case 'no_show':
                            $q->orWhere('action', 'like', '%no_show%')
                              ->orWhere('action', 'like', '%no-show%')
                              ->orWhere('description', 'like', '%no show%')
                              ->orWhere('description', 'like', '%no_show%');
                            break;
                    }
                }
            });
        }

        // actor role filter
        if ($actorRoleFilter !== '') {
            $query->where('actor_role', 'like', "%{$actorRoleFilter}%");
        }

        // created at interval filters
        if ($createdFrom !== '') {
            $query->whereDate('created_at', '>=', $createdFrom);
        }
        if ($createdTo !== '') {
            $query->whereDate('created_at', '<=', $createdTo);
        }

        $logs = $query->paginate(15)->appends($request->query());

        // Attach actor_user info to each appointment log for frontend display (username if available)
        $logs->getCollection()->transform(function ($log) {
            if ($log->actor_id) {
                try {
                    $u = User::find($log->actor_id);
                    if ($u) {
                        $log->actor_user = [
                            'id' => $u->id,
                            'name' => $u->name ?? null,
                            'email' => $u->email ?? null,
                            'username' => $u->username ?? null,
                        ];
                    } else {
                        $log->actor_user = null;
                    }
                } catch (\Throwable $e) {
                    $log->actor_user = null;
                }
            }
            return $log;
        });

        return Inertia::render('Admin/Logs/Appointments/Index', [
            'logs' => $logs,
            'filters' => [
                'search_field' => $searchField,
                'search_query' => $searchQuery,
                'statuses' => $statuses,
                'created_from' => $createdFrom,
                'created_to' => $createdTo,
                'actor_role' => $actorRoleFilter,
            ],
        ]);
    }

    public function appointmentsShow(Log $log)
    {
        // ensure it's an appointment log
        if ($log->target_type !== 'Appointment') {
            abort(404);
        }

        return Inertia::render('Admin/Logs/Appointments/Show', [
            'log' => $log,
        ]);
    }

    // Sessions logs
    public function sessionsIndex(Request $request)
    {
        $query = Log::query()
            ->with(['appointment', 'appointment.patient.patientProfile', 'appointment.psychologist.psychologistProfile'])
            ->where('target_type', 'AppointmentSession')
            ->whereIn('action', ['created_session_status', 'updated_session_status'])
            ->whereRaw("COALESCE(LOWER(actor_role), '') != ?", ['laravellogs'])
            ->orderBy('id', 'desc');

        // server-driven search: support search_field + search_query
        $searchField = strtolower(trim((string) $request->input('search_field', 'id')));
        $searchQuery = trim((string) $request->input('search_query', ''));

        // additional server-side filters: statuses, created_from/to, actor_role
        $rawStatuses = $request->input('statuses', []);
        if (is_string($rawStatuses)) {
            $rawStatuses = array_filter(array_map('trim', explode(',', $rawStatuses)));
        }
        $allowedStatuses = ['active', 'completed'];
        $statuses = collect(is_array($rawStatuses) ? $rawStatuses : [])
            ->map(fn($v) => strtolower(trim((string)$v)))
            ->filter(fn($v) => in_array($v, $allowedStatuses, true))
            ->values()
            ->all();

        $createdFrom = trim((string) $request->input('created_from', ''));
        $createdTo = trim((string) $request->input('created_to', ''));
        $actorRoleFilter = trim((string) $request->input('actor_role', ''));

        if ($searchQuery !== '') {
            $q = $searchQuery;
            switch ($searchField) {
                case 'id':
                    // Match any log id containing the typed digits (autocomplete-style).
                    $digits = preg_replace('/\D/', '', $q);
                    if ($digits !== '') {
                        $query->where('id', 'like', "%{$digits}%");
                    }
                    break;
                case 'actor':
                    $query->leftJoin('users', 'logs.actor_id', '=', 'users.id');
                    if (ctype_digit($q)) {
                        $query->where('logs.actor_id', $q);
                    } else {
                        $query->where(function ($s) use ($q) {
                            $s->where('users.name', 'like', "%{$q}%")
                              ->orWhere('users.email', 'like', "%{$q}%");
                        });
                    }
                    $query->select('logs.*');
                    break;
                case 'status':
                    $query->where(function ($s) use ($q) {
                        $s->where('action', 'like', "%{$q}%")
                          ->orWhere('description', 'like', "%{$q}%");
                    });
                    break;
                case 'action':
                    $query->where('action', 'like', "%{$q}%");
                    break;
                default:
                    $query->where(function ($s) use ($q) {
                        $s->where('action', 'like', "%{$q}%")
                          ->orWhere('users.email', 'like', "%{$q}%")
                          ->orWhere('users.name', 'like', "%{$q}%");
                    });
            }
        }

        // apply status filters (mapped to session status actions)
        if (!empty($statuses)) {
            $query->where(function ($q) use ($statuses) {
                foreach ($statuses as $s) {
                    if ($s === 'active') {
                        $q->orWhere('action', 'created_session_status');
                    } elseif ($s === 'completed') {
                        $q->orWhere('action', 'updated_session_status');
                    }
                }
            });
        }

        // actor role filter
        if ($actorRoleFilter !== '') {
            $query->where('actor_role', 'like', "%{$actorRoleFilter}%");
        }

        // created at interval filters
        if ($createdFrom !== '') {
            $query->whereDate('created_at', '>=', $createdFrom);
        }
        if ($createdTo !== '') {
            $query->whereDate('created_at', '<=', $createdTo);
        }

        $logs = $query->paginate(15)->appends($request->query());

        return Inertia::render('Admin/Logs/Sessions/Index', [
            'logs' => $logs,
            'filters' => [
                'search_field' => $searchField,
                'search_query' => $searchQuery,
                'statuses' => $statuses,
                'created_from' => $createdFrom,
                'created_to' => $createdTo,
                'actor_role' => $actorRoleFilter,
            ],
        ]);
    }

    public function sessionsShow(Log $log)
    {
        if ($log->target_type !== 'AppointmentSession') {
            abort(404);
        }

        $sessionPayload = null;
        try {
            $s = AppointmentSession::find($log->target_id);
            if ($s) {
                $patient = null;
                $psychologist = null;
                try {
                    // Prefer appointment data attached to the Log if available (more reliable).
                    $appt = $log->appointment ?? $s->appointment ?? null;
                    if ($appt) {
                        // Patient
                        $p = $appt->patient ?? null;
                        if ($p) {
                            $patient = [
                                'id' => $p->id,
                                'name' => $p->name ?? null,
                                'email' => $p->email ?? null,
                                'profile' => $p->patientProfile ? [
                                    'first_name' => $p->patientProfile->first_name ?? null,
                                    'last_name' => $p->patientProfile->last_name ?? null,
                                ] : null,
                            ];
                        }

                        // Psychologist
                        $ph = $appt->psychologist ?? null;
                        if ($ph) {
                            $psychologist = [
                                'id' => $ph->id,
                                'name' => $ph->name ?? null,
                                'email' => $ph->email ?? null,
                                'profile' => $ph->psychologistProfile ? [
                                    'first_name' => $ph->psychologistProfile->first_name ?? null,
                                    'last_name' => $ph->psychologistProfile->last_name ?? null,
                                ] : null,
                            ];
                        }
                    }
                } catch (\Throwable $e) {
                    // ignore relation issues
                }

                $sessionPayload = [
                    'id' => $s->id,
                    'appointment_id' => $s->appointment_id,
                    'room_id' => $s->room_id,
                    'started_at' => $s->started_at ? $s->started_at->toDateTimeString() : null,
                    'ended_at' => $s->ended_at ? $s->ended_at->toDateTimeString() : null,
                    'patient_joined_at' => $s->patient_joined_at ? $s->patient_joined_at->toDateTimeString() : null,
                    'psychologist_joined_at' => $s->psychologist_joined_at ? $s->psychologist_joined_at->toDateTimeString() : null,
                    'patient_left_at' => $s->patient_left_at ? $s->patient_left_at->toDateTimeString() : null,
                    'psychologist_left_at' => $s->psychologist_left_at ? $s->psychologist_left_at->toDateTimeString() : null,
                    'duration_minutes' => $s->duration_minutes,
                    'status' => $s->status,
                    'patient' => $patient,
                    'psychologist' => $psychologist,
                ];
            }
        } catch (\Throwable $e) {
            $sessionPayload = null;
        }

        return Inertia::render('Admin/Logs/Sessions/Show', [
            'log' => $log,
            'session' => $sessionPayload,
        ]);
    }

    public function sessionsRelated(Request $request, Log $log)
    {
        if ($log->target_type !== 'AppointmentSession') {
            return response()->json(['error' => 'Not a session log'], 400);
        }

        $related = Log::query()
            ->where('target_type', 'AppointmentSession')
            ->where('target_id', $log->target_id)
            ->where('created_at', '<', $log->created_at)
            ->orderBy('created_at', 'desc')
            ->get();

        $mapped = $related->map(function ($l) {
            return [
                'id' => $l->id,
                'action' => $l->action,
                'description' => $l->description,
                'created_at' => $l->created_at ? $l->created_at->toDateTimeString() : null,
                'status' => $this->extractStatusFromLog($l),
                'actor_role' => $l->actor_role ?? null,
                'actor_id' => $l->actor_id ?? null,
            ];
        });

        $session = null;
        try {
            $s = AppointmentSession::find($log->target_id);
            if ($s) {
                $session = [
                    'id' => $s->id,
                    'appointment_id' => $s->appointment_id,
                    'room_id' => $s->room_id,
                    'started_at' => $s->started_at ? $s->started_at->toDateTimeString() : null,
                    'ended_at' => $s->ended_at ? $s->ended_at->toDateTimeString() : null,
                    'patient_joined_at' => $s->patient_joined_at ? $s->patient_joined_at->toDateTimeString() : null,
                    'psychologist_joined_at' => $s->psychologist_joined_at ? $s->psychologist_joined_at->toDateTimeString() : null,
                    'patient_left_at' => $s->patient_left_at ? $s->patient_left_at->toDateTimeString() : null,
                    'psychologist_left_at' => $s->psychologist_left_at ? $s->psychologist_left_at->toDateTimeString() : null,
                    'duration_minutes' => $s->duration_minutes,
                    'status' => $s->status,
                    'patient_in_room' => $s->patient_in_room,
                    'psychologist_in_room' => $s->psychologist_in_room,
                ];
            }
        } catch (\Throwable $e) {
            $session = null;
        }

        return response()->json([
            'session' => $session,
            'logs' => $mapped,
        ]);
    }

    /**
     * Return earlier logs for the same appointment (created before the given log).
     * JSON response used by the modal to show context.
     */
    public function appointmentsRelated(Request $request, Log $log)
    {
        if ($log->target_type !== 'Appointment') {
            return response()->json(['error' => 'Not an appointment log'], 400);
        }

        $related = Log::query()
            ->where('target_type', 'Appointment')
            ->where('target_id', $log->target_id)
            ->where('created_at', '<', $log->created_at)
            ->orderBy('created_at', 'desc')
            ->get();

        $mapped = $related->map(function ($l) {
            return [
                'id' => $l->id,
                'action' => $l->action,
                'description' => $l->description,
                'created_at' => $l->created_at ? $l->created_at->toDateTimeString() : null,
                'status' => $this->extractStatusFromLog($l),
                'actor_role' => $l->actor_role ?? null,
                'actor_id' => $l->actor_id ?? null,
            ];
        });

        // also include brief appointment info if available
        $appointment = null;
        try {
            $appt = $log->appointment;
            if ($appt) {
                $appointment = [
                    'id' => $appt->id,
                    'scheduled_start' => $appt->scheduled_start ? $appt->scheduled_start->toDateTimeString() : null,
                    'patient' => $appt->patient ? ($appt->patient->name ?? null) : null,
                    'psychologist' => $appt->psychologist ? ($appt->psychologist->name ?? null) : null,
                ];
            }
        } catch (\Throwable $e) {
            $appointment = null;
        }

        return response()->json([
            'appointment' => $appointment,
            'logs' => $mapped,
        ]);
    }

    // Payouts logs
    public function payoutsIndex(Request $request)
    {
        $query = Log::query()
            ->orderBy('id', 'desc')
            ->where('target_type', 'PsychologistPayout')
            ->whereRaw("COALESCE(LOWER(actor_role), '') != ?", ['laravellogs']);

        // server-driven search: support search_field + search_query + search_date
        $searchField = strtolower(trim((string) $request->input('search_field', 'id')));
        $searchQuery = trim((string) $request->input('search_query', ''));
        $searchDate = trim((string) $request->input('search_date', ''));

        // additional server-side filters: statuses, created_from/to, actor_role
        $rawStatuses = $request->input('statuses', []);
        if (is_string($rawStatuses)) {
            $rawStatuses = array_filter(array_map('trim', explode(',', $rawStatuses)));
        }
        $allowedStatuses = ['paid', 'pending', 'on_hold', 'refunded'];
        $statuses = collect(is_array($rawStatuses) ? $rawStatuses : [])
            ->map(fn($v) => strtolower(trim((string)$v)))
            ->filter(fn($v) => in_array($v, $allowedStatuses, true))
            ->values()
            ->all();

        $createdFrom = trim((string) $request->input('created_from', ''));
        $createdTo = trim((string) $request->input('created_to', ''));
        $actorRoleFilter = trim((string) $request->input('actor_role', ''));

        if ($searchQuery !== '' || $searchDate !== '') {
            $q = $searchQuery;
            switch ($searchField) {
                case 'id':
                    // Match any log id containing the typed digits (autocomplete-style).
                    $digits = preg_replace('/\D/', '', $q);
                    if ($digits !== '') {
                        $query->where('id', 'like', "%{$digits}%");
                    }
                    break;
                case 'psychologist':
                    // search actor user fields or meta/description for psychologist name
                    $query->leftJoin('users', 'logs.actor_id', '=', 'users.id');
                    if (ctype_digit($q)) {
                        $query->where('logs.actor_id', $q);
                    } else {
                        $query->where(function ($s) use ($q) {
                            $s->where('users.name', 'like', "%{$q}%")
                              ->orWhere('users.email', 'like', "%{$q}%")
                              ->orWhere('logs.description', 'like', "%{$q}%")
                              ->orWhere('logs.meta', 'like', "%{$q}%");
                        });
                    }
                    // ensure we still select log columns for pagination/models
                    $query->select('logs.*');
                    break;
                case 'status':
                    $query->where(function ($s) use ($q) {
                        $s->where('action', 'like', "%{$q}%")
                          ->orWhere('description', 'like', "%{$q}%");
                    });
                    break;
                case 'date':
                    if ($searchDate !== '') {
                        $query->whereDate('created_at', $searchDate);
                    }
                    break;
                default:
                    $query->where(function ($qq) use ($q) {
                        $qq->where('id', 'like', "%{$q}%")
                           ->orWhere('target_id', 'like', "%{$q}%")
                           ->orWhere('description', 'like', "%{$q}%")
                           ->orWhere('action', 'like', "%{$q}%");
                    });
            }
        }

        // apply status filters (map to action/description tokens)
        if (!empty($statuses)) {
            $query->where(function ($qq) use ($statuses) {
                foreach ($statuses as $s) {
                    if ($s === 'paid') {
                        $qq->orWhere('action', 'like', '%paid%')->orWhere('description', 'like', '%paid%');
                    } elseif ($s === 'on_hold') {
                        $qq->orWhere('action', 'like', '%on_hold%')->orWhere('description', 'like', '%on hold%')->orWhere('description', 'like', '%on-hold%');
                    } elseif ($s === 'refunded') {
                        $qq->orWhere('action', 'like', '%refund%')->orWhere('description', 'like', '%refund%');
                    } elseif ($s === 'pending') {
                        $qq->orWhere('action', 'like', '%created%')->orWhere('description', 'like', '%created%')->orWhere('action', 'like', '%pending%');
                    } else {
                        $qq->orWhere('action', 'like', "%{$s}%")->orWhere('description', 'like', "%{$s}%");
                    }
                }
            });
        }

        // actor role filter
        if ($actorRoleFilter !== '') {
            $query->where('actor_role', 'like', "%{$actorRoleFilter}%");
        }

        // created at interval filters
        if ($createdFrom !== '') {
            $query->whereDate('created_at', '>=', $createdFrom);
        }
        if ($createdTo !== '') {
            $query->whereDate('created_at', '<=', $createdTo);
        }

        $logs = $query->paginate(15)->appends($request->query());

        return Inertia::render('Admin/Logs/Payouts/Index', [
            'logs' => $logs,
            'filters' => [
                'search_field' => $searchField,
                'search_query' => $searchQuery,
                'search_date' => $searchDate,
                'statuses' => $statuses,
                'created_from' => $createdFrom,
                'created_to' => $createdTo,
                'actor_role' => $actorRoleFilter,
            ],
        ]);
    }

    public function payoutsShow(Log $log)
    {
        if ($log->target_type !== 'PsychologistPayout') {
            abort(404);
        }

        return Inertia::render('Admin/Logs/Payouts/Show', [
            'log' => $log,
        ]);
    }

    public function payoutsRelated(Request $request, Log $log)
    {
        if ($log->target_type !== 'PsychologistPayout') {
            return response()->json(['error' => 'Not a payout log'], 400);
        }

        $related = Log::query()
            ->where('target_type', 'PsychologistPayout')
            ->where('target_id', $log->target_id)
            ->where('created_at', '<', $log->created_at)
            ->orderBy('created_at', 'desc')
            ->get();

        $mapped = $related->map(function ($l) {
            return [
                'id' => $l->id,
                'action' => $l->action,
                'description' => $l->description,
                'created_at' => $l->created_at ? $l->created_at->toDateTimeString() : null,
                'status' => $this->extractStatusFromLog($l),
                'actor_role' => $l->actor_role ?? null,
                'actor_id' => $l->actor_id ?? null,
            ];
        });

        // include brief payout info if available
        $payout = null;
        try {
            $pp = \App\Models\PsychologistPayout::find($log->target_id);
            if ($pp) {
                $payout = [
                    'id' => $pp->id,
                    'appointment_id' => $pp->appointment_id,
                    'psychologist_id' => $pp->psychologist_id,
                    'gross_amount' => (string) $pp->gross_amount,
                    'net_amount' => (string) $pp->net_amount,
                    'status' => $pp->status,
                ];
            }
        } catch (\Throwable $e) {
            $payout = null;
        }

        return response()->json([
            'payout' => $payout,
            'logs' => $mapped,
        ]);
    }

    private function extractStatusFromLog(Log $l)
    {
        // Normalize to one of: pending, confirmed, completed, cancelled, no_show
        $action = strtolower((string) $l->action);
        if (str_contains($action, 'created')) return 'pending';
        if (str_contains($action, 'confirm')) return 'confirmed';
        if (str_contains($action, 'complete') || str_contains($action, 'completed')) return 'completed';
        if (str_contains($action, 'cancel')) return 'cancelled';
        if (str_contains($action, 'no_show') || str_contains($action, 'no-show') || str_contains($action, 'no show')) return 'no_show';
        if (str_contains($action, 'refund') || str_contains($action, 'refunded')) return 'refunded';
        if (str_contains($action, 'paid') || str_contains($action, 'paid_at')) return 'paid';
        if (str_contains($action, 'on_hold') || str_contains($action, 'on-hold') || str_contains($action, 'on hold')) return 'on_hold';

        // try to parse description for common status tokens
        $desc = strtolower((string) $l->description);
        if (preg_match('/\b(no[_\- ]?show)\b/', $desc, $m)) return 'no_show';
        if (preg_match('/\b(cancel|cancelled|canceled)\b/', $desc, $m)) return 'cancelled';
        if (preg_match('/\b(created|pending)\b/', $desc, $m)) return 'pending';
        if (preg_match('/\b(confirm|confirmed)\b/', $desc, $m)) return 'confirmed';
        if (preg_match('/\b(complete|completed)\b/', $desc, $m)) return 'completed';

        // try to parse "to <status>" or "status <status>"
        if (preg_match('/to\s+([a-z_\-]+)/', $desc, $m)) {
            $s = str_replace('-', '_', $m[1]);
            return $this->normalizeStatusToken($s) ?? $s;
        }
        if (preg_match('/status\s+([a-z_\-]+)/', $desc, $m)) {
            $s = str_replace('-', '_', $m[1]);
            return $this->normalizeStatusToken($s) ?? $s;
        }

        return null;
    }

    private function normalizeStatusToken(string $token)
    {
        $t = strtolower(trim($token));
        $map = [
            'pending' => 'pending',
            'created' => 'pending',
            'confirm' => 'confirmed',
            'confirmed' => 'confirmed',
            'complete' => 'completed',
            'completed' => 'completed',
            'cancel' => 'cancelled',
            'canceled' => 'cancelled',
            'cancelled' => 'cancelled',
            'no_show' => 'no_show',
            'no-show' => 'no_show',
            'no show' => 'no_show',
            'noshow' => 'no_show',
            'refund' => 'refunded',
            'refunded' => 'refunded',
            'refunds' => 'refunded',
            'paid' => 'paid',
            'paid_at' => 'paid',
            'on_hold' => 'on_hold',
            'on-hold' => 'on_hold',
            'on hold' => 'on_hold',
        ];

        return $map[$t] ?? ($map[str_replace('-', '_', $t)] ?? null);
    }

    // Placeholder methods for psychologist logs — return empty listing for now.
    public function psychologistsIndex(Request $request)
    {
        $query = Log::query()->where('target_type', 'PsychologistProfile')->whereRaw("COALESCE(LOWER(actor_role), '') != ?", ['laravellogs'])->orderBy('id', 'desc');

        // server-driven search
        $searchField = strtolower(trim((string) $request->input('search_field', 'id')));
        $searchQuery = trim((string) $request->input('search_query', ''));
        $actorRoleFilter = trim((string) $request->input('actor_role', ''));
        $createdFrom = trim((string) $request->input('created_from', ''));
        $createdTo = trim((string) $request->input('created_to', ''));
        if ($searchQuery !== '') {
            $q = $searchQuery;
            switch ($searchField) {
                case 'id':
                    if (ctype_digit($q)) {
                        $query->where('id', $q);
                    } else {
                        $query->where('id', 'like', "%{$q}%");
                    }
                    break;
                case 'actor':
                    $query->leftJoin('users', 'logs.actor_id', '=', 'users.id');
                    $query->where(function ($s) use ($q) {
                        $s->where('logs.actor_id', 'like', "%{$q}%")
                          ->orWhere('users.name', 'like', "%{$q}%")
                          ->orWhere('users.email', 'like', "%{$q}%");
                    });
                    $query->select('logs.*');
                    break;
                case 'action':
                    $query->where('action', 'like', "%{$q}%");
                    break;
                default:
                    $query->where(function ($s) use ($q) {
                        $s->where('action', 'like', "%{$q}%")
                          ->orWhere('users.email', 'like', "%{$q}%")
                          ->orWhere('users.name', 'like', "%{$q}%");
                    });
            }
        }

        if ($actorRoleFilter !== '') {
            $query->where('actor_role', 'like', "%{$actorRoleFilter}%");
        }

        if ($createdFrom !== '') {
            $query->whereDate('created_at', '>=', $createdFrom);
        }
        if ($createdTo !== '') {
            $query->whereDate('created_at', '<=', $createdTo);
        }

        $logs = $query->paginate(15)->appends($request->query());

        // Attach brief psychologist + user info to each paginated log item when possible
        $logs->getCollection()->transform(function ($log) {
            if ($log->target_type === 'PsychologistProfile') {
                try {
                    $profile = PsychologistProfile::with('user')->find($log->target_id);
                    if ($profile) {
                        $log->psychologist = [
                            'id' => $profile->id,
                            'user' => $profile->user ? [
                                'id' => $profile->user->id,
                                'name' => $profile->user->name ?? null,
                                'email' => $profile->user->email ?? null,
                                'username' => $profile->user->username ?? null,
                            ] : null,
                            'name' => $profile->name ?? null,
                        ];
                    } else {
                        $log->psychologist = null;
                    }
                } catch (\Throwable $e) {
                    $log->psychologist = null;
                }
            }

            // Attach actor user info (admin or other user actors)
            if ($log->actor_id) {
                try {
                    $u = User::find($log->actor_id);
                    if ($u) {
                        $log->actor_user = [
                            'id' => $u->id,
                            'name' => $u->name ?? null,
                            'email' => $u->email ?? null,
                            'username' => $u->username ?? null,
                        ];
                    } else {
                        $log->actor_user = null;
                    }
                } catch (\Throwable $e) {
                    $log->actor_user = null;
                }
            }
            return $log;
        });

        return Inertia::render('Admin/Logs/Psychologists/Index', [
            'logs' => $logs,
            'filters' => [
                'search_field' => $searchField,
                'search_query' => $searchQuery,
                'actor_role' => $actorRoleFilter,
                'created_from' => $createdFrom,
                'created_to' => $createdTo,
            ],
        ]);
    }

    public function psychologistsShow(Log $log)
    {
        if ($log->target_type !== 'PsychologistProfile') {
            abort(404);
        }
        return Inertia::render('Admin/Logs/Psychologists/Show', [ 'log' => $log ]);
    }

    // Patients logs (mirror of psychologists)
    public function patientsIndex(Request $request)
    {
        $query = Log::query()
            ->where('target_type', 'PatientProfile')
            ->whereRaw("COALESCE(LOWER(actor_role), '') != ?", ['laravellogs'])
            ->orderBy('id', 'desc');

        // server-driven search: support search_field + search_query
        $searchField = strtolower(trim((string) $request->input('search_field', 'id')));
        $searchQuery = trim((string) $request->input('search_query', ''));

        // additional server-side filters: statuses, created_from/to, actor_role
        $rawStatuses = $request->input('statuses', []);
        if (is_string($rawStatuses)) {
            $rawStatuses = array_filter(array_map('trim', explode(',', $rawStatuses)));
        }
        $allowedStatuses = ['pending', 'confirmed', 'completed', 'cancelled', 'no_show'];
        $statuses = collect(is_array($rawStatuses) ? $rawStatuses : [])
            ->map(fn($v) => strtolower(trim((string)$v)))
            ->filter(fn($v) => in_array($v, $allowedStatuses, true))
            ->values()
            ->all();

        $createdFrom = trim((string) $request->input('created_from', ''));
        $createdTo = trim((string) $request->input('created_to', ''));
        $actorRoleFilter = trim((string) $request->input('actor_role', ''));

        if ($searchQuery !== '') {
            $q = $searchQuery;
            switch ($searchField) {
                case 'id':
                    $digits = preg_replace('/\D/', '', $q);
                    if ($digits !== '') {
                        $query->where('id', 'like', "%{$digits}%");
                    } else {
                        $query->where('id', 'like', "%{$q}%");
                    }
                    break;
                case 'actor':
                    $query->leftJoin('users', 'logs.actor_id', '=', 'users.id');
                    if (ctype_digit($q)) {
                        $query->where('logs.actor_id', $q);
                    } else {
                        $query->where(function ($s) use ($q) {
                            $s->where('users.name', 'like', "%{$q}%")
                              ->orWhere('users.email', 'like', "%{$q}%");
                            if (Schema::hasColumn('users', 'username')) {
                                $s->orWhere('users.username', 'like', "%{$q}%");
                            }
                        });
                    }
                    // ensure we still select log columns for pagination/models
                    $query->select('logs.*');
                    break;
                case 'status':
                    $query->where(function ($s) use ($q) {
                        $s->where('action', 'like', "%{$q}%")
                          ->orWhere('description', 'like', "%{$q}%");
                    });
                    break;
                case 'action':
                    $query->where('action', 'like', "%{$q}%");
                    break;
                default:
                    $query->where(function ($s) use ($q) {
                        $s->where('action', 'like', "%{$q}%")
                          ->orWhere('users.email', 'like', "%{$q}%")
                          ->orWhere('users.name', 'like', "%{$q}%");
                        if (Schema::hasColumn('users', 'username')) {
                            $s->orWhere('users.username', 'like', "%{$q}%");
                        }
                    });
            }
        }

        // apply status filters (map to action/description tokens)
        if (!empty($statuses)) {
            $query->where(function ($q) use ($statuses) {
                foreach ($statuses as $s) {
                    switch ($s) {
                        case 'pending':
                            $q->orWhere('action', 'like', '%created%')
                              ->orWhere('description', 'like', '%pending%');
                            break;
                        case 'confirmed':
                            $q->orWhere('action', 'like', '%confirm%')
                              ->orWhere('description', 'like', '%confirm%');
                            break;
                        case 'completed':
                            $q->orWhere('action', 'like', '%complete%')
                              ->orWhere('description', 'like', '%complete%');
                            break;
                        case 'cancelled':
                            $q->orWhere('action', 'like', '%cancel%')
                              ->orWhere('description', 'like', '%cancel%');
                            break;
                        case 'no_show':
                            $q->orWhere('action', 'like', '%no_show%')
                              ->orWhere('action', 'like', '%no-show%')
                              ->orWhere('description', 'like', '%no show%')
                              ->orWhere('description', 'like', '%no_show%');
                            break;
                    }
                }
            });
        }

        // actor role filter
        if ($actorRoleFilter !== '') {
            $query->where('actor_role', 'like', "%{$actorRoleFilter}%");
        }

        // created at interval filters
        if ($createdFrom !== '') {
            $query->whereDate('created_at', '>=', $createdFrom);
        }
        if ($createdTo !== '') {
            $query->whereDate('created_at', '<=', $createdTo);
        }

        $logs = $query->paginate(15)->appends($request->query());

        // Attach brief patient + user info to each paginated log item when possible
        $logs->getCollection()->transform(function ($log) {
            if ($log->target_type === 'PatientProfile') {
                try {
                    $profile = PatientProfile::with('user')->find($log->target_id);
                    if ($profile) {
                        $log->patient = [
                            'id' => $profile->id,
                            'user' => $profile->user ? [
                                'id' => $profile->user->id,
                                'name' => $profile->user->name ?? null,
                                'email' => $profile->user->email ?? null,
                                'username' => $profile->user->username ?? null,
                            ] : null,
                            'name' => $profile->name ?? null,
                        ];
                    } else {
                        $log->patient = null;
                    }
                } catch (\Throwable $e) {
                    $log->patient = null;
                }
            }

            // Attach actor user info (admin or other user actors)
            if ($log->actor_id) {
                try {
                    $u = User::find($log->actor_id);
                    if ($u) {
                        $log->actor_user = [
                            'id' => $u->id,
                            'name' => $u->name ?? null,
                            'email' => $u->email ?? null,
                            'username' => $u->username ?? null,
                        ];
                    } else {
                        $log->actor_user = null;
                    }
                } catch (\Throwable $e) {
                    $log->actor_user = null;
                }
            }
            return $log;
        });

        return Inertia::render('Admin/Logs/Patients/Index', [
            'logs' => $logs,
            'filters' => [
                'search_field' => $searchField,
                'search_query' => $searchQuery,
                'statuses' => $statuses,
                'created_from' => $createdFrom,
                'created_to' => $createdTo,
                'actor_role' => $actorRoleFilter,
            ],
        ]);
    }

    public function patientsShow(Log $log)
    {
        if ($log->target_type !== 'PatientProfile') {
            abort(404);
        }
        return Inertia::render('Admin/Logs/Patients/Show', [ 'log' => $log ]);
    }
}
