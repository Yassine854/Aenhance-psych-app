<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PsychologistPayout;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Log;

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
        $searchField = strtolower(trim((string) $request->input('search_field', 'id')));
        $searchQuery = trim((string) $request->input('search_query', ''));
        $searchDate = trim((string) $request->input('search_date', ''));
        $createdFrom = trim((string) $request->input('created_from', ''));
        $createdTo = trim((string) $request->input('created_to', ''));

        $rawStatuses = $request->input('statuses', []);
        if (is_string($rawStatuses)) {
            $rawStatuses = array_filter(array_map('trim', explode(',', $rawStatuses)));
        }

        $allowedStatuses = ['pending', 'paid', 'on_hold', 'refund'];
        $statuses = collect(is_array($rawStatuses) ? $rawStatuses : [])
            ->map(fn ($value) => strtolower(trim((string) $value)))
            ->filter(fn ($value) => in_array($value, $allowedStatuses, true))
            ->values()
            ->all();

        $payoutsQuery = PsychologistPayout::query()
            ->with(['psychologist:id,name', 'appointment.patient:id,name']);

        if (! empty($statuses)) {
            $payoutsQuery->whereIn('status', $statuses);
        }

        if ($createdFrom !== '') {
            $payoutsQuery->whereDate('created_at', '>=', $createdFrom);
        }

        if ($createdTo !== '') {
            $payoutsQuery->whereDate('created_at', '<=', $createdTo);
        }

        if ($searchField === 'date') {
            if ($searchDate !== '') {
                $payoutsQuery->whereDate('updated_at', $searchDate);
            }
        } elseif ($searchQuery !== '') {
            if ($searchField === 'psychologist') {
                $payoutsQuery->whereHas('psychologist', function ($q) use ($searchQuery) {
                    $q->where('name', 'like', '%'.$searchQuery.'%');
                });
            } else {
                $payoutsQuery->where('id', 'like', '%'.$searchQuery.'%');
            }
        }

        $payouts = $payoutsQuery
            ->orderByDesc('id')
            ->paginate(15)
            ->appends($request->query());

        $mappedItems = collect($payouts->items())->map(function (PsychologistPayout $p) {
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
        })->values()->all();

        $payouts = new \Illuminate\Pagination\LengthAwarePaginator(
            $mappedItems,
            $payouts->total(),
            $payouts->perPage(),
            $payouts->currentPage(),
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return Inertia::render('Admin/Payouts/Index', [
            'payouts' => $payouts,
            'status' => session('status'),
            'filters' => [
                'search_field' => in_array($searchField, ['id', 'psychologist', 'date'], true) ? $searchField : 'id',
                'search_query' => $searchQuery,
                'search_date' => $searchDate,
                'statuses' => $statuses,
                'created_from' => $createdFrom,
                'created_to' => $createdTo,
            ],
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

        $oldStatus = (string) $payout->status;
        $update = ['status' => $newStatus];
        if ($newStatus === 'paid') {
            $update['paid_at'] = now();
        }
        if ($newStatus === 'refund') {
            $update['refund_at'] = now();
        }
        $payout->update($update);

        // Record activity log for this payout status change
        try {
            Log::record([
                'actor_id' => $user ? $user->id : null,
                'actor_role' => $user && method_exists($user, 'isAdmin') && $user->isAdmin() ? 'admin' : null,
                'action' => 'status_changed',
                'target_type' => 'PsychologistPayout',
                'target_id' => $payout->id,
                'status' => $newStatus,
                'description' => sprintf('Status changed from %s to %s', $oldStatus ?: 'unknown', $newStatus),
            ]);
        } catch (\Throwable $e) {
            // intentionally ignore logging failures
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

        // Record activity logs for each payout changed
        try {
            foreach ($ids as $id) {
                Log::record([
                    'actor_id' => $user ? $user->id : null,
                    'actor_role' => $user && method_exists($user, 'isAdmin') && $user->isAdmin() ? 'admin' : null,
                    'action' => 'status_changed',
                    'target_type' => 'PsychologistPayout',
                    'target_id' => $id,
                    'status' => $status,
                    'description' => sprintf('Status changed to %s', $status),
                ]);
            }
        } catch (\Throwable $e) {
            // ignore logging errors
        }

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json(['updated' => count($ids)]);
        }

        return redirect()->route('admin.payouts.index')->with('status', 'Payouts updated.');
    }
}
