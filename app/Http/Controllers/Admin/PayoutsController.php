<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PsychologistPayout;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PayoutsController extends Controller
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
        $payouts = PsychologistPayout::query()
            ->with(['psychologist:id,name', 'appointment.patient:id,name'])
            ->orderByDesc('id')
            ->paginate(15);

        $payouts->setCollection(
            $payouts->getCollection()->map(function (PsychologistPayout $p) {
                return [
                    'id' => $p->id,
                    'appointment_id' => $p->appointment_id,
                    'psychologist' => $p->psychologist ? [
                        'id' => $p->psychologist->id,
                        'name' => $p->psychologist->name,
                    ] : null,
                    'patient' => $p->appointment && $p->appointment->patient ? [
                        'id' => $p->appointment->patient->id,
                        'name' => $p->appointment->patient->name,
                    ] : null,
                    'gross_amount' => (string) $p->gross_amount,
                    'platform_fee' => (string) $p->platform_fee,
                    'net_amount' => (string) $p->net_amount,
                    'currency' => $p->currency ?? 'TND',
                    'status' => (string) $p->status,
                    'estimated_availability' => optional($p->estimated_availability)->toISOString() ?? ($p->estimated_availability ? (string) $p->estimated_availability : null),
                    'paid_at' => optional($p->paid_at)->toISOString() ?? ($p->paid_at ? (string) $p->paid_at : null),
                    'created_at' => optional($p->created_at)->toISOString() ?? (string) $p->created_at,
                    'updated_at' => optional($p->updated_at)->toISOString() ?? (string) $p->updated_at,
                ];
            })
        );

        return Inertia::render('Admin/Payouts/Index', [
            'payouts' => $payouts,
            'status' => session('status'),
        ]);
    }

    public function show(Request $request, PsychologistPayout $payout)
    {
        $payout->load(['psychologist', 'appointment.patient']);

        $payload = [
            'id' => $payout->id,
            'appointment_id' => $payout->appointment_id,
            'psychologist' => $payout->psychologist ? [
                'id' => $payout->psychologist->id,
                'name' => $payout->psychologist->name,
            ] : null,
            'patient' => $payout->appointment && $payout->appointment->patient ? [
                'id' => $payout->appointment->patient->id,
                'name' => $payout->appointment->patient->name,
            ] : null,
            'gross_amount' => (string) $payout->gross_amount,
            'platform_fee' => (string) $payout->platform_fee,
            'net_amount' => (string) $payout->net_amount,
            'currency' => $payout->currency ?? 'TND',
            'status' => (string) $payout->status,
            'estimated_availability' => optional($payout->estimated_availability)->toISOString() ?? ($payout->estimated_availability ? (string) $payout->estimated_availability : null),
            'paid_at' => optional($payout->paid_at)->toISOString() ?? ($payout->paid_at ? (string) $payout->paid_at : null),
            'created_at' => optional($payout->created_at)->toISOString() ?? (string) $payout->created_at,
            'updated_at' => optional($payout->updated_at)->toISOString() ?? (string) $payout->updated_at,
        ];

        return Inertia::render('Admin/Payouts/Show', [
            'payout' => $payload,
        ]);
    }

    public function updateStatus(Request $request, PsychologistPayout $payout)
    {
        $user = $request->user();
        if (! $user || ! method_exists($user, 'isAdmin') || ! $user->isAdmin()) {
            abort(403);
        }

        $data = $request->validate([
            'status' => ['required', 'in:pending,paid,on_hold,refund'],
        ]);

        $newStatus = $data['status'];

        if ($newStatus === 'paid') {
            $payout->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);
        } elseif ($newStatus === 'refund') {
            $payout->update([
                'status' => 'refund',
                'refund_at' => now(),
            ]);
        } else {
            $payout->update([
                'status' => $newStatus,
            ]);
        }

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json(['payout' => $payout->fresh()]);
        }

        return redirect()->route('admin.payouts.index')->with('status', 'Payout updated.');
    }

    public function bulkUpdate(Request $request)
    {
        $user = $request->user();
        if (! $user || ! method_exists($user, 'isAdmin') || ! $user->isAdmin()) {
            abort(403);
        }

        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:psychologist_payouts,id'],
            'status' => ['required', 'in:pending,paid,on_hold,refund'],
        ]);

        $ids = $data['ids'];
        $status = $data['status'];

        $update = ['status' => $status];
        if ($status === 'paid') {
            $update['paid_at'] = now();
        }
        if ($status === 'refund') {
            $update['refund_at'] = now();
        }

        PsychologistPayout::whereIn('id', $ids)->update($update);

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json(['updated' => count($ids)]);
        }

        return redirect()->route('admin.payouts.index')->with('status', 'Payouts updated.');
    }
}
