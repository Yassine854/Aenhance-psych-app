<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use App\Services\ActivityLogger;

class AdminAppointmentController extends Controller
{
    private function normalizeStatus(?string $value): string
    {
        return strtolower(trim((string) $value));
    }

    private function allowedAppointmentTransitions(string $current): array
    {
        // Guided transitions (terminal states cannot be changed)
        return match ($current) {
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['completed', 'no_show', 'cancelled'],
            'completed', 'no_show', 'cancelled' => [],
            default => [],
        };
    }

    public function index(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! method_exists($user, 'isAdmin') || ! $user->isAdmin()) {
            return redirect()->route('dashboard');
        }

        $appointments = Appointment::query()
            ->with([
                'patient:id,name,email,role',
                'psychologist:id,name,email,role',
                'payments' => function ($q) {
                    $q->latest('id');
                },
            ])
            ->orderByDesc('scheduled_start')
            ->paginate(15)
            ->withQueryString();

        $payload = $appointments->through(function (Appointment $a) {
            $latestPayment = $a->payments->first();

            return [
                'id' => $a->id,
                'patient' => $a->patient ? [
                    'id' => $a->patient->id,
                    'name' => $a->patient->name,
                ] : null,
                'psychologist' => $a->psychologist ? [
                    'id' => $a->psychologist->id,
                    'name' => $a->psychologist->name,
                ] : null,
                'scheduled_start' => optional($a->scheduled_start)->toISOString() ?? (string) $a->scheduled_start,
                'scheduled_end' => optional($a->scheduled_end)->toISOString() ?? (string) $a->scheduled_end,
                'status' => (string) $a->status,
                'price' => $a->price,
                'currency' => (string) ($a->currency ?: 'TND'),

                'canceled_by' => $a->canceled_by,
                'canceled_by_user_id' => $a->canceled_by_user_id,
                'cancellation_reason' => $a->cancellation_reason,
                'canceled_at' => optional($a->canceled_at)->toISOString() ?? ($a->canceled_at ? (string) $a->canceled_at : null),

                'payment' => $latestPayment ? [
                    'id' => $latestPayment->id,
                    'status' => (string) $latestPayment->status,
                    'provider' => (string) $latestPayment->provider,
                    'amount' => $latestPayment->amount,
                    'currency' => (string) $latestPayment->currency,
                    'paid_at' => optional($latestPayment->paid_at)->toISOString() ?? ($latestPayment->paid_at ? (string) $latestPayment->paid_at : null),
                ] : null,
            ];
        });

        return Inertia::render('Admin/Appointments/Index', [
            'appointments' => $payload,
            'status' => session('status'),
        ]);
    }

    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! method_exists($user, 'isAdmin') || ! $user->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'appointment_status' => ['nullable', 'in:pending,confirmed,completed,cancelled,no_show'],
            'payment_status' => ['nullable', 'in:pending,paid,failed,refunded'],
            'cancellation_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $appointmentStatus = $validated['appointment_status'] ?? null;
        $paymentStatus = $validated['payment_status'] ?? null;

        try {
            DB::transaction(function () use ($user, $appointment, $appointmentStatus, $paymentStatus, $validated) {
                $currentAppointmentStatus = $this->normalizeStatus((string) $appointment->status);
                $targetAppointmentStatus = $appointmentStatus ? $this->normalizeStatus($appointmentStatus) : null;
                $targetPaymentStatus = $paymentStatus ? $this->normalizeStatus($paymentStatus) : null;

                $latestPayment = Payment::query()->where('appointment_id', $appointment->id)->latest('id')->first();
                $currentPaymentStatus = $latestPayment ? $this->normalizeStatus((string) $latestPayment->status) : '';

                // If admin marks payment as paid on a pending appointment, we auto-confirm.
                if ($targetPaymentStatus === 'paid' && ! $targetAppointmentStatus && $currentAppointmentStatus === 'pending') {
                    $targetAppointmentStatus = 'confirmed';
                }

                // Do not allow setting appointment back to pending.
                if ($targetAppointmentStatus === 'pending' && $currentAppointmentStatus !== 'pending') {
                    throw ValidationException::withMessages([
                        'appointment_status' => 'You cannot move an appointment back to Pending.',
                    ]);
                }

                // Enforce guided transitions (terminal states are locked).
                if ($targetAppointmentStatus && $targetAppointmentStatus !== $currentAppointmentStatus) {
                    $allowed = $this->allowedAppointmentTransitions($currentAppointmentStatus);
                    if (! in_array($targetAppointmentStatus, $allowed, true)) {
                        throw ValidationException::withMessages([
                            'appointment_status' => 'This status change is not allowed for the current appointment state.',
                        ]);
                    }
                }

                // Payment rules based on appointment state.
                $effectiveAppointmentStatus = $targetAppointmentStatus ?: $currentAppointmentStatus;

                // Don’t allow paid on cancelled/completed/no_show.
                if ($targetPaymentStatus === 'paid' && in_array($effectiveAppointmentStatus, ['cancelled'], true)) {
                    throw ValidationException::withMessages([
                        'payment_status' => 'You cannot mark a cancelled appointment as Paid. Refund instead if needed.',
                    ]);
                }

                // Confirmed/completed/no_show should be paid (do not allow forcing pending/failed/refunded while confirming).
                if ($targetAppointmentStatus && in_array($targetAppointmentStatus, ['confirmed', 'completed', 'no_show'], true)) {
                    if ($targetPaymentStatus && $targetPaymentStatus !== 'paid') {
                        throw ValidationException::withMessages([
                            'payment_status' => 'Confirmed/Completed/No-show appointments must be Paid.',
                        ]);
                    }
                }

                // Refund is only allowed if there is a paid payment already and appointment is cancelled or no_show.
                if ($targetPaymentStatus === 'refunded') {
                    if (! in_array($effectiveAppointmentStatus, ['cancelled', 'no_show'], true)) {
                        throw ValidationException::withMessages([
                            'payment_status' => 'Refund is only allowed for cancelled or missed appointments.',
                        ]);
                    }
                    if ($currentPaymentStatus !== 'paid') {
                        throw ValidationException::withMessages([
                            'payment_status' => 'Refund is only allowed if the latest payment is Paid.',
                        ]);
                    }
                }

                // Apply appointment update.
                if ($targetAppointmentStatus) {
                    if ($targetAppointmentStatus === 'cancelled') {
                        $appointment->update([
                            'status' => 'cancelled',
                            'canceled_by' => 'admin',
                            'canceled_by_user_id' => $user->id,
                            'cancellation_reason' => $validated['cancellation_reason'] ?? 'Cancelled by admin',
                            'canceled_at' => now(),
                        ]);
                    } else {
                        $appointment->update([
                            'status' => $targetAppointmentStatus,
                        ]);
                    }
                }

                // Smart behavior: confirming/completing/no_show without a payment creates a paid payment (manual admin).
                if (in_array($effectiveAppointmentStatus, ['confirmed', 'completed', 'no_show'], true) && ! $targetPaymentStatus) {
                    if (! $latestPayment) {
                        $created = Payment::create([
                            'appointment_id' => $appointment->id,
                            'amount' => $appointment->price,
                            'currency' => (string) ($appointment->currency ?: 'TND'),
                            'provider' => 'manual_admin',
                            'status' => 'paid',
                            'paid_at' => now(),
                        ]);
                        if ($created) {
                            ActivityLogger::log($user->id, $user->role ?? null, 'created_payment', 'Payment', $created->id, 'Admin created manual payment for appointment '.$appointment->id);
                        }
                    }
                }

                // Apply payment update.
                if ($targetPaymentStatus) {
                    $payment = $latestPayment;

                    if (! $payment) {
                        $payment = Payment::create([
                            'appointment_id' => $appointment->id,
                            'amount' => $appointment->price,
                            'currency' => (string) ($appointment->currency ?: 'TND'),
                            'provider' => 'manual_admin',
                            'status' => $targetPaymentStatus,
                            'paid_at' => $targetPaymentStatus === 'paid' ? now() : null,
                        ]);
                        if ($payment) {
                            ActivityLogger::log($user->id, $user->role ?? null, 'created_payment', 'Payment', $payment->id, 'Admin created payment with status '.$targetPaymentStatus.' for appointment '.$appointment->id);
                        }
                    } else {
                        $oldStatus = $payment->status;
                        $payment->update([
                            'status' => $targetPaymentStatus,
                            'paid_at' => $targetPaymentStatus === 'paid' ? now() : ($targetPaymentStatus === 'refunded' ? $payment->paid_at : null),
                        ]);
                        ActivityLogger::log($user->id, $user->role ?? null, 'updated_payment', 'Payment', $payment->id, 'Payment status changed from '.$oldStatus.' to '.$targetPaymentStatus.' for appointment '.$appointment->id);
                    }
                }
            });
        } catch (ValidationException $e) {
            return redirect()->back()->with('error', $e->errors()['appointment_status'][0] ?? $e->errors()['payment_status'][0] ?? 'Invalid update.');
        }

        return redirect()->back()->with('status', 'Appointment updated.');
    }
}
