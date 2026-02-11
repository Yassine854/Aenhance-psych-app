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
        $payments = Payment::query()
            ->with(['appointment.patient:id,name', 'appointment.psychologist:id,name'])
            ->orderByDesc('id')
            ->paginate(15);

        $payments->setCollection(
            $payments->getCollection()->map(function (Payment $p) {
                return [
                    'id' => $p->id,
                    'appointment_id' => $p->appointment_id,
                    'amount' => $p->amount,
                    'currency' => $p->currency ?? 'TND',
                    'provider' => $p->provider,
                    'status' => (string) $p->status,
                    'transaction_id' => $p->transaction_id,
                    'paid_at' => optional($p->paid_at)->toISOString() ?? ($p->paid_at ? (string) $p->paid_at : null),
                    'created_at' => optional($p->created_at)->toISOString() ?? (string) $p->created_at,
                    'patient' => $p->appointment && $p->appointment->patient ? [
                        'id' => $p->appointment->patient->id,
                        'name' => $p->appointment->patient->name,
                    ] : null,
                    'psychologist' => $p->appointment && $p->appointment->psychologist ? [
                        'id' => $p->appointment->psychologist->id,
                        'name' => $p->appointment->psychologist->name,
                    ] : null,
                ];
            })
        );

        return Inertia::render('Admin/Payments/Index', [
            'payments' => $payments,
            'status' => session('status'),
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
