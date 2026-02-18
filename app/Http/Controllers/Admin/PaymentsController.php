<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Http\Response;

class PaymentsController extends Controller
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

        $allowedStatuses = ['pending', 'paid', 'failed', 'refunded'];
        $statuses = collect(is_array($rawStatuses) ? $rawStatuses : [])
            ->map(fn ($value) => strtolower(trim((string) $value)))
            ->filter(fn ($value) => in_array($value, $allowedStatuses, true))
            ->values()
            ->all();

        $paymentsQuery = Payment::query()
            ->with(['appointment.patient:id,name', 'appointment.psychologist:id,name']);

        if (! empty($statuses)) {
            $paymentsQuery->whereIn('status', $statuses);
        }

        if ($createdFrom !== '') {
            $paymentsQuery->whereDate('created_at', '>=', $createdFrom);
        }

        if ($createdTo !== '') {
            $paymentsQuery->whereDate('created_at', '<=', $createdTo);
        }

        if ($searchField === 'date') {
            if ($searchDate !== '') {
                $paymentsQuery->whereDate('created_at', $searchDate);
            }
        } elseif ($searchQuery !== '') {
            if ($searchField === 'patient') {
                $paymentsQuery->whereHas('appointment.patient', function ($q) use ($searchQuery) {
                    $q->where('name', 'like', '%'.$searchQuery.'%');
                });
            } elseif ($searchField === 'psychologist') {
                $paymentsQuery->whereHas('appointment.psychologist', function ($q) use ($searchQuery) {
                    $q->where('name', 'like', '%'.$searchQuery.'%');
                });
            } else {
                $paymentsQuery->where('id', 'like', '%'.$searchQuery.'%');
            }
        }

        $payments = $paymentsQuery
            ->orderByDesc('id')
            ->paginate(15)
            ->appends($request->query());

        $mappedItems = collect($payments->items())->map(function (Payment $p) {
            return [
                'id' => $p->id,
                'appointment_id' => $p->appointment_id,
                'amount' => $p->amount,
                'currency' => $p->currency ?? 'TND',
                'provider' => $p->provider,
                'status' => (string) $p->status,
                'transaction_id' => $p->transaction_id,
                'refund_reason' => $p->refund_reason,
                'paid_at' => optional($p->paid_at)->toISOString() ?? ($p->paid_at ? (string) $p->paid_at : null),
                'created_at' => optional($p->created_at)->toISOString() ?? (string) $p->created_at,
                'updated_at' => optional($p->updated_at)->toISOString() ?? (string) $p->updated_at,
                'appointment' => $p->appointment ? [
                    'id' => $p->appointment->id,
                    'scheduled_start' => optional($p->appointment->scheduled_start)->toISOString() ?? ($p->appointment->scheduled_start ? (string) $p->appointment->scheduled_start : null),
                ] : null,
                'patient' => $p->appointment && $p->appointment->patient ? [
                    'id' => $p->appointment->patient->id,
                    'name' => $p->appointment->patient->name,
                ] : null,
                'psychologist' => $p->appointment && $p->appointment->psychologist ? [
                    'id' => $p->appointment->psychologist->id,
                    'name' => $p->appointment->psychologist->name,
                ] : null,
            ];
        })->values()->all();

        $payments = new \Illuminate\Pagination\LengthAwarePaginator(
            $mappedItems,
            $payments->total(),
            $payments->perPage(),
            $payments->currentPage(),
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return Inertia::render('Admin/Payments/Index', [
            'payments' => $payments,
            'status' => session('status'),
            'filters' => [
                'search_field' => in_array($searchField, ['id', 'patient', 'psychologist', 'date'], true) ? $searchField : 'id',
                'search_query' => $searchQuery,
                'search_date' => $searchDate,
                'statuses' => $statuses,
                'created_from' => $createdFrom,
                'created_to' => $createdTo,
            ],
        ]);
    }

    public function show(Request $request, Payment $payment)
    {
        $payment->load(['appointment.patient', 'appointment.psychologist']);

        $payload = [
            'id' => $payment->id,
            'appointment_id' => $payment->appointment_id,
            'amount' => $payment->amount,
            'currency' => $payment->currency ?? 'TND',
            'provider' => $payment->provider,
            'status' => (string) $payment->status,
            'transaction_id' => $payment->transaction_id,
            'payment_method' => $payment->payment_method,
            'failure_reason' => $payment->failure_reason,
            'refund_reason' => $payment->refund_reason,
            'paid_at' => optional($payment->paid_at)->toISOString() ?? ($payment->paid_at ? (string) $payment->paid_at : null),
            'created_at' => optional($payment->created_at)->toISOString() ?? (string) $payment->created_at,
            'appointment' => $payment->appointment ? [
                'id' => $payment->appointment->id,
                'patient' => $payment->appointment->patient ? [
                    'id' => $payment->appointment->patient->id,
                    'name' => $payment->appointment->patient->name,
                ] : null,
                'psychologist' => $payment->appointment->psychologist ? [
                    'id' => $payment->appointment->psychologist->id,
                    'name' => $payment->appointment->psychologist->name,
                ] : null,
                'scheduled_start' => optional($payment->appointment->scheduled_start)->toISOString() ?? ($payment->appointment->scheduled_start ? (string) $payment->appointment->scheduled_start : null),
            ] : null,
        ];

        return Inertia::render('Admin/Payments/Show', [
            'payment' => $payload,
        ]);
    }

    public function updateStatus(Request $request, Payment $payment)
    {
        $user = $request->user();
        if (! $user || ! method_exists($user, 'isAdmin') || ! $user->isAdmin()) {
            abort(403);
        }

        $data = $request->validate([
            'status' => ['required', 'in:pending,paid,failed,refunded'],
            'refund_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $newStatus = $data['status'];

        if ($newStatus === 'paid') {
            $payment->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);
        } elseif ($newStatus === 'refunded') {
            $payment->update([
                'status' => 'refunded',
                'refund_reason' => $data['refund_reason'] ?? null,
            ]);
        } else {
            $payment->update([
                'status' => $newStatus,
            ]);
        }

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json(['payment' => $payment->fresh()]);
        }

        return redirect()->route('admin.payments.index')->with('status', 'Payment updated.');
    }
}
