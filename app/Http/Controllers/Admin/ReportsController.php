<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;
use App\Models\PatientProfile;
use App\Models\PsychologistProfile;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportsController extends Controller
{
    public function __construct()
    {
        $this->middleware(function (Request $request, $next) {
            $user = $request->user();
            abort_unless($user && method_exists($user, 'isAdmin') && $user->isAdmin(), 403);
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $reports = Report::query()
            ->orderByDesc('id')
            ->paginate(15);

        $reports->setCollection(
            $reports->getCollection()->map(function (Report $r) {
                // resolve display name for reporter and reported without using morph relation
                $resolve = function ($type, $id) {
                    if (! $type || ! $id) return null;

                    // try common model lookups in order that match how the frontend submits IDs
                    // reporter_id is usually a User id (auth user), reported_id is often a PsychologistProfile id
                    $name = null;

                    // try User first
                    $user = User::find($id);
                    if ($user) {
                        $name = $user->name ?? ($user->full_name ?? null);
                        return ['id' => $user->id, 'type' => (string) $type, 'name' => $name];
                    }

                    // try PatientProfile
                    $p = PatientProfile::find($id);
                    if ($p) {
                        $name = $p->user?->name ?? ($p->first_name ? trim(($p->first_name . ' ' . ($p->last_name ?? ''))) : null);
                        return ['id' => $p->id, 'type' => (string) $type, 'name' => $name];
                    }

                    // try PsychologistProfile
                    $pp = PsychologistProfile::find($id);
                    if ($pp) {
                        $name = $pp->user?->name ?? ($pp->first_name ? trim(($pp->first_name . ' ' . ($pp->last_name ?? ''))) : null);
                        return ['id' => $pp->id, 'type' => (string) $type, 'name' => $name];
                    }

                    return ['id' => $id, 'type' => (string) $type, 'name' => null];
                };

                return [
                    'id' => $r->id,
                    'reporter' => $resolve($r->reporter_type, $r->reporter_id),
                    'reported' => $resolve($r->reported_type, $r->reported_id),
                    'reason' => $r->reason,
                    'proof_image' => $r->proof_image,
                    'is_resolved' => (bool) $r->is_resolved,
                    'created_at' => optional($r->created_at)->toISOString() ?? (string) $r->created_at,
                    'resolved_at' => optional($r->resolved_at)->toISOString() ?? (string) $r->resolved_at,
                ];
            })
        );

        return Inertia::render('Admin/Reports/Index', [
            'reports' => $reports,
            'status' => session('status'),
        ]);
    }

    public function show(Request $request, Report $report)
    {
        // build payload without relying on morph relations
        $resolveSingle = function ($type, $id) {
            if (! $type || ! $id) return null;
            $user = User::find($id);
            if ($user) return ['id' => $user->id, 'name' => $user->name ?? null, 'type' => (string) $type];
            $p = PatientProfile::find($id);
            if ($p) return ['id' => $p->id, 'name' => $p->user?->name ?? ($p->first_name ? trim($p->first_name . ' ' . ($p->last_name ?? '')) : null), 'type' => (string) $type];
            $pp = PsychologistProfile::find($id);
            if ($pp) return ['id' => $pp->id, 'name' => $pp->user?->name ?? ($pp->first_name ? trim($pp->first_name . ' ' . ($pp->last_name ?? '')) : null), 'type' => (string) $type];
            return ['id' => $id, 'name' => null, 'type' => (string) $type];
        };

        $payload = [
            'id' => $report->id,
            'reporter' => $resolveSingle($report->reporter_type, $report->reporter_id),
            'reported' => $resolveSingle($report->reported_type, $report->reported_id),
            'reason' => $report->reason,
            'proof_image' => $report->proof_image,
            'is_resolved' => (bool) $report->is_resolved,
            'created_at' => optional($report->created_at)->toISOString() ?? (string) $report->created_at,
            'resolved_at' => optional($report->resolved_at)->toISOString() ?? (string) $report->resolved_at,
        ];

        return Inertia::render('Admin/Reports/Show', [
            'report' => $payload,
        ]);
    }

    public function update(Request $request, Report $report)
    {
        $user = $request->user();
        if (! $user || ! method_exists($user, 'isAdmin') || ! $user->isAdmin()) {
            abort(403);
        }

        $data = $request->validate([
            'is_resolved' => ['required', 'boolean'],
        ]);

        if ($data['is_resolved']) {
            // First mark resolved which will update `updated_at`
            $report->update([
                'is_resolved' => true,
            ]);

            // Refresh to get the new `updated_at`, then set `resolved_at` to that exact value
            $report->refresh();

            // Save `resolved_at` without touching timestamps so `updated_at` remains unchanged
            $report->timestamps = false;
            $report->resolved_at = $report->updated_at;
            $report->save();
            $report->timestamps = true;
        } else {
            $report->update([
                'is_resolved' => false,
                'resolved_at' => null,
            ]);
        }

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json(['report' => $report->fresh()]);
        }

        return redirect()->route('admin.reports.index')->with('status', 'Report updated.');
    }
}
