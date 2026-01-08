<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Payment;
use App\Models\PsychologistProfile;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AppointmentController extends Controller
{
    private const SESSION_MINUTES = 60;
    private const BOOKING_DAYS_AHEAD = 90;

    // List appointments (role-based)
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'PATIENT') {
            return $user->patientAppointments;
        }

        if ($user->role === 'PSYCHOLOGIST') {
            return $user->psychologistAppointments;
        }

        // Admin sees all
        return Appointment::all();
    }

    /**
     * Booking screen for a patient to choose a slot.
     */
    public function book(Request $request, PsychologistProfile $psychologist_profile): Response|RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! method_exists($user, 'isPatient') || ! $user->isPatient()) {
            return redirect()->route('dashboard');
        }

        $profile = PsychologistProfile::query()
            ->with(['user', 'availabilities', 'specialisations'])
            ->whereKey($psychologist_profile->id)
            ->firstOrFail();

        if (! $profile->user || ! $profile->is_approved) {
            return redirect()->route('services.consultation');
        }

        $tz = config('app.timezone') ?: 'UTC';
        $now = Carbon::now($tz);
        $startDay = $now->copy()->startOfDay();
        $endDay = $startDay->copy()->addDays(self::BOOKING_DAYS_AHEAD - 1)->endOfDay();

        $existing = Appointment::query()
            ->where('psychologist_id', $profile->user_id)
            ->whereBetween('scheduled_start', [$startDay, $endDay])
            ->whereNotIn('status', ['cancelled'])
            ->get(['scheduled_start', 'scheduled_end']);

        $existingRanges = $existing
            ->map(fn ($a) => [
                Carbon::parse($a->scheduled_start, $tz),
                Carbon::parse($a->scheduled_end, $tz),
            ])
            ->values();

        $availabilities = $profile->availabilities
            ->sortBy(['day_of_week', 'start_time'])
            ->values();

        $days = [];
        for ($i = 0; $i < self::BOOKING_DAYS_AHEAD; $i++) {
            $date = $startDay->copy()->addDays($i);
            $dow = (int) $date->dayOfWeek; // 0=Sunday

            $slots = [];
            foreach ($availabilities as $availability) {
                if ((int) $availability->day_of_week !== $dow) {
                    continue;
                }

                $startTime = Carbon::parse($date->toDateString().' '.$availability->start_time, $tz);
                $endTime = Carbon::parse($date->toDateString().' '.$availability->end_time, $tz);

                // Build 60-minute slots.
                $cursor = $startTime->copy();
                while ($cursor->copy()->addMinutes(self::SESSION_MINUTES)->lte($endTime)) {
                    $slotStart = $cursor->copy();
                    $slotEnd = $cursor->copy()->addMinutes(self::SESSION_MINUTES);

                    // Skip past times.
                    if ($slotStart->lt($now)) {
                        $cursor->addMinutes(self::SESSION_MINUTES);
                        continue;
                    }

                    $conflicts = false;
                    foreach ($existingRanges as [$busyStart, $busyEnd]) {
                        // overlap if start < busyEnd AND end > busyStart
                        if ($slotStart->lt($busyEnd) && $slotEnd->gt($busyStart)) {
                            $conflicts = true;
                            break;
                        }
                    }

                    if (! $conflicts) {
                        $slots[] = [
                            'start_iso' => $slotStart->toIso8601String(),
                            'end_iso' => $slotEnd->toIso8601String(),
                            'start_time' => $slotStart->format('H:i'),
                            'end_time' => $slotEnd->format('H:i'),
                        ];
                    }

                    $cursor->addMinutes(self::SESSION_MINUTES);
                }
            }

            $days[] = [
                'date' => $date->toDateString(),
                'day_of_week' => $dow,
                'slots' => $slots,
            ];
        }

        return Inertia::render('Patient/Appointments/Book', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'authUser' => $user,
            'status' => session('status'),
            'psychologistProfile' => [
                'id' => $profile->id,
                'user_id' => $profile->user_id,
                'first_name' => $profile->first_name,
                'last_name' => $profile->last_name,
                'profile_image_url' => $profile->profile_image_url,
                'price_per_session' => $profile->price_per_session,
                'specialisations' => $profile->specialisations
                    ->sortBy('name')
                    ->values()
                    ->map(fn ($s) => ['id' => $s->id, 'name' => $s->name]),
            ],
            'days' => $days,
            'sessionMinutes' => self::SESSION_MINUTES,
        ]);
    }

    /**
     * Patient appointments list UI.
     */
    public function patientIndex(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! method_exists($user, 'isPatient') || ! $user->isPatient()) {
            return redirect()->route('dashboard');
        }

        $appointments = Appointment::query()
            ->where('patient_id', $user->id)
            ->orderBy('scheduled_start')
            ->get([
                'id',
                'patient_id',
                'psychologist_id',
                'scheduled_start',
                'scheduled_end',
                'status',
                'price',
                'currency',
                'created_at',
            ]);

        $psychologistUserIds = $appointments->pluck('psychologist_id')->unique()->values();

        $profilesByUserId = PsychologistProfile::query()
            ->whereIn('user_id', $psychologistUserIds)
            ->get(['id', 'user_id', 'first_name', 'last_name'])
            ->keyBy('user_id');

        $appointmentsPayload = $appointments->map(function (Appointment $a) use ($profilesByUserId) {
            $p = $profilesByUserId->get($a->psychologist_id);
            $psychName = null;
            if ($p) {
                $psychName = trim(($p->first_name ?? '').' '.($p->last_name ?? '')) ?: null;
            }

            return [
                'id' => $a->id,
                'psychologist_id' => $a->psychologist_id,
                'psychologist_profile_id' => $p?->id,
                'psychologist_name' => $psychName,
                'scheduled_start' => optional($a->scheduled_start)->toISOString() ?? (string) $a->scheduled_start,
                'scheduled_end' => optional($a->scheduled_end)->toISOString() ?? (string) $a->scheduled_end,
                'status' => (string) $a->status,
                'price' => $a->price,
                'currency' => (string) ($a->currency ?: 'TND'),
            ];
        })->values();

        return Inertia::render('Patient/Appointments/Index', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'authUser' => $user,
            'status' => session('status'),
            'appointments' => $appointmentsPayload,
        ]);
    }

    // Create appointment (Patient)
    public function store(Request $request)
    {
        $user = $request->user();
        if (! $user || ! method_exists($user, 'isPatient') || ! $user->isPatient()) {
            abort(403);
        }

        $validated = $request->validate([
            'psychologist_id' => ['required', 'exists:users,id'],
            'scheduled_start' => ['required', 'date'],
        ]);

        $tz = config('app.timezone') ?: 'UTC';
        $start = Carbon::parse($validated['scheduled_start'], $tz);
        $end = $start->copy()->addMinutes(self::SESSION_MINUTES);

        // Find the related psychologist profile for pricing.
        $profile = PsychologistProfile::query()
            ->where('user_id', $validated['psychologist_id'])
            ->first();

        $price = $profile?->price_per_session ?? 0;

        // Avoid double booking (basic overlap protection).
        $overlap = Appointment::query()
            ->where('psychologist_id', $validated['psychologist_id'])
            ->whereNotIn('status', ['cancelled'])
            ->where(function ($q) use ($start, $end) {
                $q->where('scheduled_start', '<', $end)
                    ->where('scheduled_end', '>', $start);
            })
            ->exists();

        if ($overlap) {
            return back()->withErrors([
                'scheduled_start' => 'This time is no longer available. Please choose another slot.',
            ]);
        }

        Appointment::create([
            'patient_id' => $user->id,
            'psychologist_id' => (int) $validated['psychologist_id'],
            'scheduled_start' => $start,
            'scheduled_end' => $end,
            'status' => 'pending',
            'price' => $price,
            'currency' => 'TND',
        ]);

        return redirect()->back()->with('status', 'Appointment requested successfully.');
    }

    // Update appointment status (Psychologist/Admin)
    public function update(Request $request, Appointment $appointment)
    {
        $user = $request->user();
        if (! $user) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled,no_show',
            'cancellation_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $requestedStatus = (string) $validated['status'];

        // Patient can only confirm their OWN pending appointment (static pay).
        if (method_exists($user, 'isPatient') && $user->isPatient()) {
            if ((int) $appointment->patient_id !== (int) $user->id) {
                abort(403);
            }
            if ($requestedStatus !== 'confirmed') {
                abort(403);
            }
            if ((string) $appointment->status !== 'pending') {
                return back()->withErrors([
                    'status' => 'Only pending appointments can be confirmed.',
                ]);
            }

            DB::transaction(function () use ($appointment) {
                $appointment->update(['status' => 'confirmed']);

                // Create (or update) a payment record.
                // For now: skip payment gateway and mark as paid immediately.
                $payment = Payment::query()
                    ->where('appointment_id', $appointment->id)
                    ->latest('id')
                    ->first();

                $payload = [
                    'appointment_id' => $appointment->id,
                    'amount' => $appointment->price,
                    'currency' => (string) ($appointment->currency ?: 'TND'),
                    'provider' => 'manual',
                    'status' => 'paid',
                    'paid_at' => now(),
                ];

                if ($payment) {
                    $payment->update($payload);
                } else {
                    Payment::create($payload);
                }
            });

            return redirect()->back()->with('status', 'Payment successful. Appointment confirmed.');
        }

        // Psychologist/Admin can update statuses (existing behavior).
        if ($requestedStatus === 'cancelled') {
            $canceledBy = null;
            if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
                $canceledBy = 'admin';
            } elseif (method_exists($user, 'isPsychologist') && $user->isPsychologist()) {
                $canceledBy = 'psychologist';
            }

            $appointment->update([
                'status' => 'cancelled',
                'canceled_by' => $canceledBy,
                'canceled_by_user_id' => $user->id,
                'cancellation_reason' => $validated['cancellation_reason'] ?? null,
                'canceled_at' => now(),
            ]);
        } else {
            $appointment->update(['status' => $requestedStatus]);
        }

        if ($request->wantsJson()) {
            return $appointment;
        }

        return redirect()->back()->with('status', 'Appointment updated successfully.');
    }

    // Cancel appointment (Patient/Admin)
    public function destroy(Request $request, Appointment $appointment)
    {
        $user = $request->user();
        if (! $user) {
            abort(403);
        }

        $validated = $request->validate([
            'cancellation_reason' => ['nullable', 'string', 'max:255'],
        ]);

        // Patient can only cancel their OWN pending appointment.
        if (method_exists($user, 'isPatient') && $user->isPatient()) {
            if ((int) $appointment->patient_id !== (int) $user->id) {
                abort(403);
            }
            if ((string) $appointment->status !== 'pending') {
                return back()->withErrors([
                    'status' => 'Only pending appointments can be cancelled.',
                ]);
            }
        }

        $canceledBy = null;
        if (method_exists($user, 'isPatient') && $user->isPatient()) {
            $canceledBy = 'patient';
        } elseif (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            $canceledBy = 'admin';
        } elseif (method_exists($user, 'isPsychologist') && $user->isPsychologist()) {
            $canceledBy = 'psychologist';
        }

        $reason = $validated['cancellation_reason'] ?? null;
        if (! $reason) {
            $reason = match ($canceledBy) {
                'patient' => 'Cancelled by patient',
                'psychologist' => 'Cancelled by psychologist',
                'admin' => 'Cancelled by admin',
                default => 'Cancelled',
            };
        }

        $appointment->update([
            'status' => 'cancelled',
            'canceled_by' => $canceledBy,
            'canceled_by_user_id' => $user->id,
            'cancellation_reason' => $reason,
            'canceled_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Appointment cancelled']);
        }

        return redirect()->back()->with('status', 'Appointment cancelled successfully.');
    }
}
