<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Log;
use App\Models\PsychologistProfile;
use App\Models\PatientProfile;
use App\Models\User;

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
            ->orderBy('id', 'desc');

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($s) use ($q) {
                $s->where('action', 'like', "%{$q}%")
                  ->orWhere('description', 'like', "%{$q}%")
                  ->orWhere('actor_role', 'like', "%{$q}%")
                  ->orWhere('target_id', 'like', "%{$q}%");
            });
        }

        $logs = $query->paginate(15)->appends($request->query());

        return Inertia::render('Admin/Logs/Appointments/Index', [
            'logs' => $logs,
            'filters' => $request->only(['q']),
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

    private function extractStatusFromLog(Log $l)
    {
        // Normalize to one of: pending, confirmed, completed, cancelled, no_show
        $action = strtolower((string) $l->action);
        if (str_contains($action, 'created')) return 'pending';
        if (str_contains($action, 'confirm')) return 'confirmed';
        if (str_contains($action, 'complete') || str_contains($action, 'completed')) return 'completed';
        if (str_contains($action, 'cancel')) return 'cancelled';
        if (str_contains($action, 'no_show') || str_contains($action, 'no-show') || str_contains($action, 'no show')) return 'no_show';

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
            return $this->normalizeStatusToken($s);
        }
        if (preg_match('/status\s+([a-z_\-]+)/', $desc, $m)) {
            $s = str_replace('-', '_', $m[1]);
            return $this->normalizeStatusToken($s);
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
        ];

        return $map[$t] ?? ($map[str_replace('-', '_', $t)] ?? null);
    }

    // Placeholder methods for psychologist logs — return empty listing for now.
    public function psychologistsIndex(Request $request)
    {
        $query = Log::query()->where('target_type', 'PsychologistProfile')->orderBy('id', 'desc');
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
                    $u = User::select('id', 'name', 'username', 'email')->find($log->actor_id);
                    if ($u) {
                        $log->actor_user = [
                            'id' => $u->id,
                            'name' => $u->name ?? null,
                            'username' => $u->username ?? null,
                            'email' => $u->email ?? null,
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

        return Inertia::render('Admin/Logs/Psychologists/Index', [ 'logs' => $logs, 'filters' => $request->only(['q']) ]);
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
        $query = Log::query()->where('target_type', 'PatientProfile')->orderBy('id', 'desc');
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
                    $u = User::select('id', 'name', 'username', 'email')->find($log->actor_id);
                    if ($u) {
                        $log->actor_user = [
                            'id' => $u->id,
                            'name' => $u->name ?? null,
                            'username' => $u->username ?? null,
                            'email' => $u->email ?? null,
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

        return Inertia::render('Admin/Logs/Patients/Index', [ 'logs' => $logs, 'filters' => $request->only(['q']) ]);
    }

    public function patientsShow(Log $log)
    {
        if ($log->target_type !== 'PatientProfile') {
            abort(404);
        }
        return Inertia::render('Admin/Logs/Patients/Show', [ 'log' => $log ]);
    }
}
