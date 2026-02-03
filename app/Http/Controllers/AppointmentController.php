<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentSession;
use App\Models\Payment;
use App\Models\PsychologistProfile;
use App\Services\ClicToPayClient;
use App\Services\JaaSJitsiJwt;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use App\Services\ActivityLogger;

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
            ->with(['user', 'availabilities', 'specialisations', 'expertises'])
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
                'languages' => $profile->languages ?? [],
                'bio' => $profile->bio ?? '',
                'price_per_session' => $profile->price_per_session,
                'specialisations' => $profile->specialisations
                    ->sortBy('name')
                    ->values()
                    ->map(fn ($s) => ['id' => $s->id, 'name' => $s->name]),
                'expertises' => $profile->expertises
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
            ->with(['session:id,appointment_id,room_id,status,started_at'])
            ->orderByDesc('scheduled_start')
            ->get([
                'id',
                'patient_id',
                'psychologist_id',
                'scheduled_start',
                'scheduled_end',
                'status',
                'price',
                'currency',
                'no_show_by',
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
                // A call "exists" once a session room_id exists. started_at is set only when both participants join.
                'session_room_id' => (string) ($a->session?->room_id ?: ''),
                'session_status' => (string) ($a->session?->status ?: ''),
                'session_started_at' => optional($a->session?->started_at)->toISOString() ?? ($a->session?->started_at ? (string) $a->session->started_at : null),
                'missed_by' => $a->no_show_by,
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

        $appointment = Appointment::create([
            'patient_id' => $user->id,
            'psychologist_id' => (int) $validated['psychologist_id'],
            'scheduled_start' => $start,
            'scheduled_end' => $end,
            'status' => 'pending',
            'price' => $price,
            'currency' => 'TND',
        ]);

        ActivityLogger::log($user->id, $user->role ?? null, 'created_appointment', 'Appointment', $appointment->id, 'Appointment requested');

        return redirect()->back()->with('status', 'Appointment requested successfully.');
    }

    /**
     * Start a ClickToPay payment and redirect the patient to the gateway UI.
     */
    public function startClickToPay(Request $request, Appointment $appointment)
    {
        $user = $request->user();
        if (! $user || ! method_exists($user, 'isPatient') || ! $user->isPatient()) {
            abort(403);
        }

        if ((int) $appointment->patient_id !== (int) $user->id) {
            abort(403);
        }

        if (strtolower((string) $appointment->status) !== 'pending') {
            return redirect()->back()->with('status', 'This appointment cannot be paid (not pending).');
        }

        $client = new ClicToPayClient();
        if (! $client->isConfigured()) {
            return redirect()->back()->withErrors([
                'payment' => 'ClickToPay is not configured on the server.',
            ]);
        }

        $testNo = (string) $request->input('test_no', '');
        if ($testNo !== '' && ! preg_match('/^\d{4}$/', $testNo)) {
            return redirect()->back()->withErrors([
                'payment' => 'Invalid test_no. Expected 4 digits like 0001.',
            ]);
        }

        $orderNumber = 'APT-'.$appointment->id
            .($testNo !== '' ? '-T'.$testNo : '')
            .'-'.now()->format('YmdHis');

        $returnUrl = route('payments.clictopay.return', ['appointment' => $appointment->id], true);
        $failUrl = route('payments.clictopay.fail', ['appointment' => $appointment->id], true);

        try {
            $currency = (string) ($appointment->currency ?: 'TND');

            $testDescription = $testNo !== '' ? match ($testNo) {
                '0001' => 'Transaction autorisée (VISA)',
                '0002' => 'Transaction autorisée (MasterCard)',
                '0004' => 'Plafond atteint',
                '0005' => 'Solde insuffisant',
                '0007' => 'Carte non valide',
                '0008' => 'Validité incorrecte',
                '0009' => 'CVV2 incorrecte',
                default => 'Test case '.$testNo,
            } : null;

            $result = $client->register([
                'orderNumber' => $orderNumber,
                'amount' => $client->amountToMinorUnits($appointment->price, $currency),
                'currency' => $client->currencyToIso4217Numeric($currency),
                'returnUrl' => $returnUrl,
                'failUrl' => $failUrl,
                'description' => $testDescription
                    ? ('Appointment '.$appointment->id.' - '.$testDescription)
                    : ('Appointment '.$appointment->id),
                'pageView' => 'DESKTOP',
            ]);
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors([
                'payment' => 'Could not start ClickToPay payment: '.$e->getMessage(),
            ]);
        }

        $errorCode = $result['errorCode'] ?? null;
        $formUrl = $result['formUrl'] ?? null;
        $gatewayOrderId = $result['orderId'] ?? null;

        if (! $formUrl || ($errorCode !== null && (string) $errorCode !== '0')) {
            $msg = (string) ($result['errorMessage'] ?? 'Payment registration failed.');
            return redirect()->back()->withErrors([
                'payment' => $msg,
            ]);
        }

        // Create/update a pending payment record (we will mark it paid on return).
        $payment = Payment::query()
            ->where('appointment_id', $appointment->id)
            ->latest('id')
            ->first();

        $payload = [
            'appointment_id' => $appointment->id,
            'amount' => $appointment->price,
            'currency' => (string) ($appointment->currency ?: 'TND'),
            'provider' => 'clictopay',
            'status' => 'pending',
            'transaction_id' => $gatewayOrderId ?: null,
        ];

        if ($payment) {
            if (strtolower((string) $payment->status) !== 'paid') {
                $payment->update($payload);
            }
        } else {
            Payment::create($payload);
        }

        ActivityLogger::log($user->id, $user->role ?? null, 'started_payment', 'Appointment', $appointment->id, 'Started ClickToPay payment for appointment '.$appointment->id);

        if ($testNo !== '') {
            \Illuminate\Support\Facades\Log::info('ClickToPay test case selected', [
                'appointment_id' => $appointment->id,
                'test_no' => $testNo,
                'orderNumber' => $orderNumber,
            ]);
        }

        // Tell Inertia to do a full redirect to the external gateway page.
        return Inertia::location($formUrl);
    }

    /**
     * ClickToPay redirects the user here on successful/finished payment flow.
     * We verify the gateway status and then confirm the appointment + mark payment as paid.
     */
    public function clicToPayReturn(Request $request, Appointment $appointment)
    {
        $user = $request->user();
        if (! $user || ! method_exists($user, 'isPatient') || ! $user->isPatient()) {
            return redirect()->route('login');
        }

        if ((int) $appointment->patient_id !== (int) $user->id) {
            abort(403);
        }

        $orderId = (string) ($request->query('mdOrder') ?: $request->query('orderId') ?: '');
        if ($orderId === '') {
            return redirect()->route('patient.appointments')->withErrors([
                'payment' => 'Missing ClickToPay order id (mdOrder).',
            ]);
        }

        $client = new ClicToPayClient();
        if (! $client->isConfigured()) {
            return redirect()->route('patient.appointments')->withErrors([
                'payment' => 'ClickToPay is not configured on the server.',
            ]);
        }

        try {
            $status = $client->getOrderStatusExtended($orderId);
        } catch (\Throwable $e) {
            return redirect()->route('patient.appointments')->withErrors([
                'payment' => 'Could not verify payment status: '.$e->getMessage(),
            ]);
        }

        // getOrderStatusExtended typically returns lower-case keys (orderStatus/actionCode),
        // but keep fallbacks for variants.
        $orderStatus = $status['orderStatus'] ?? ($status['OrderStatus'] ?? null);
        $actionCode = $status['actionCode'] ?? ($status['ActionCode'] ?? null);
        $errorCode = $status['errorCode'] ?? ($status['ErrorCode'] ?? null);

        // In the manual: orderStatus 2 = deposited successfully. actionCode 0 = processed successfully.
        $orderStatusStr = $orderStatus === null ? null : (string) $orderStatus;
        $actionCodeStr = $actionCode === null ? null : (string) $actionCode;
        $errorCodeStr = $errorCode === null ? null : (string) $errorCode;

        $approvalCode = (string) (
            $status['cardAuthInfo']['approvalCode']
                ?? $status['CardAuthInfo']['approvalCode']
                ?? ''
        );

        $gatewayOrderNumber = (string) (
            $status['orderNumber']
                ?? $status['OrderNumber']
                ?? ''
        );
        $cardPanMasked = (string) (
            $status['cardAuthInfo']['pan']
                ?? $status['CardAuthInfo']['pan']
                ?? ''
        );
        $cardExpiration = (string) (
            $status['cardAuthInfo']['expiration']
                ?? $status['CardAuthInfo']['expiration']
                ?? ''
        );

        $testNoFromOrder = '';
        if ($gatewayOrderNumber !== '' && preg_match('/-T(\d{4})-/', $gatewayOrderNumber, $m)) {
            $testNoFromOrder = (string) $m[1];
        }

        $testDescription = $testNoFromOrder !== '' ? match ($testNoFromOrder) {
            '0001' => 'Transaction autorisée (VISA)',
            '0002' => 'Transaction autorisée (MasterCard)',
            '0004' => 'Plafond atteint',
            '0005' => 'Solde insuffisant',
            '0007' => 'Carte non valide',
            '0008' => 'Validité incorrecte',
            '0009' => 'CVV2 incorrecte',
            default => 'Test case '.$testNoFromOrder,
        } : null;

        // If orderNumber was tagged with a test_no (T0004, ...), check whether the
        // returned masked PAN/expiration matches what the test matrix expects.
        // This catches common testing mistakes (wrong card/expiry entered).
        $expected = null;
        if ($testNoFromOrder !== '') {
            $expected = match ($testNoFromOrder) {
                '0001' => ['pan6' => '450921', 'exp_yyyymm' => '202612'],
                '0002' => ['pan6' => '544021', 'exp_yyyymm' => '202612'],
                '0004' => ['pan6' => '456894', 'exp_yyyymm' => '202512'],
                '0005' => ['pan6' => '510405', 'exp_yyyymm' => '202512'],
                '0007' => ['pan6' => '455769', 'exp_yyyymm' => '202512'],
                '0008' => ['pan6' => '450921', 'exp_yyyymm' => '202512'],
                '0009' => ['pan6' => '450921', 'exp_yyyymm' => '202612'],
                default => null,
            };
        }

        // Best-effort inference from gateway-provided masked PAN/expiration.
        // Note: we can NEVER infer CVV-only cases (0001 vs 0009) because CVV is not returned.
        $testGuess = null;
        if ($testDescription) {
            $testGuess = $testDescription;
        } elseif ($cardPanMasked !== '') {
            $pan6 = preg_match('/^(\d{6})/', $cardPanMasked, $m) ? (string) $m[1] : '';
            $exp = $cardExpiration;

            // ClickToPay typically returns YYYYMM (e.g. 201512). Keep it flexible.
            $expYyyyMm = '';
            if (preg_match('/^(\d{4})(\d{2})$/', $exp, $m2)) {
                $expYyyyMm = $m2[1].$m2[2];
            }

            $testGuess = match (true) {
                $pan6 === '456894' => 'Plafond atteint (0004)',
                $pan6 === '510405' => 'Solde insuffisant (0005)',
                $pan6 === '455769' => 'Carte non valide (0007)',
                $pan6 === '544021' => 'Transaction autorisée (MasterCard) (0002)',
                // VISA test card used in multiple cases
                $pan6 === '450921' && $expYyyyMm === '202512' => 'Validité incorrecte (0008)',
                $pan6 === '450921' && $expYyyyMm === '202612' => 'VISA success-like case (0001 or 0009; CVV-only cases are indistinguishable)',
                $pan6 === '450921' => 'VISA test card (0001/0008/0009)',
                default => null,
            };
        }

        if (is_array($expected) && $cardPanMasked !== '' && $cardExpiration !== '') {
            $pan6Actual = preg_match('/^(\d{6})/', $cardPanMasked, $m) ? (string) $m[1] : '';
            $expActual = '';
            if (preg_match('/^(\d{4})(\d{2})$/', $cardExpiration, $m2)) {
                $expActual = $m2[1].$m2[2];
            }

            $mismatch = false;
            if (($expected['pan6'] ?? '') !== '' && $pan6Actual !== '' && (string) $expected['pan6'] !== $pan6Actual) {
                $mismatch = true;
            }
            if (($expected['exp_yyyymm'] ?? '') !== '' && $expActual !== '' && (string) $expected['exp_yyyymm'] !== $expActual) {
                $mismatch = true;
            }

            if ($mismatch) {
                Log::warning('ClickToPay test case mismatch', [
                    'appointment_id' => $appointment->id,
                    'orderId' => $orderId,
                    'orderNumber' => $gatewayOrderNumber !== '' ? $gatewayOrderNumber : null,
                    'test_no' => $testNoFromOrder,
                    'expected' => $expected,
                    'actual' => [
                        'pan6' => $pan6Actual !== '' ? $pan6Actual : null,
                        'exp_yyyymm' => $expActual !== '' ? $expActual : null,
                    ],
                ]);
            }
        }

        Log::info('ClickToPay verification response', [
            'appointment_id' => $appointment->id,
            'orderId' => $orderId,
            'orderNumber' => $gatewayOrderNumber !== '' ? $gatewayOrderNumber : null,
            'test_no' => $testNoFromOrder !== '' ? $testNoFromOrder : null,
            'test_description' => $testDescription,
            'test_guess' => $testGuess,
            'orderStatus' => $orderStatusStr,
            'actionCode' => $actionCodeStr,
            'errorCode' => $errorCodeStr,
            'approvalCode_present' => $approvalCode !== '',
            'card_pan_masked' => $cardPanMasked !== '' ? $cardPanMasked : null,
            'card_expiration' => $cardExpiration !== '' ? $cardExpiration : null,
            'actionCodeDescription' => $status['actionCodeDescription'] ?? ($status['ActionCodeDescription'] ?? null),
            'errorMessage' => $status['errorMessage'] ?? ($status['ErrorMessage'] ?? null),
        ]);

        $classification = 'unknown';
        if ($orderStatusStr === '2' && $actionCodeStr === '0') {
            $classification = 'authorized';
        } elseif ($orderStatusStr === '6') {
            $classification = 'authorization_refused';
        } elseif ($actionCodeStr !== null && $actionCodeStr !== '0') {
            $classification = 'declined_action_code_'.$actionCodeStr;
        }

        Log::info('ClickToPay transaction classification', [
            'appointment_id' => $appointment->id,
            'orderId' => $orderId,
            'classification' => $classification,
            'test_description' => $testDescription,
        ]);

        // IMPORTANT: do NOT treat a missing actionCode as success.
        // Declines (e.g. wrong CVV2) can still redirect to returnUrl, so we must verify actionCode == 0.
        // Per manual: orderStatus 2 = deposited successfully.
        $isPaid = ($orderStatusStr === '2')
            && ($actionCodeStr === '0')
            && ($errorCodeStr === null || $errorCodeStr === '0');

        Log::info('ClickToPay paid decision', [
            'appointment_id' => $appointment->id,
            'orderId' => $orderId,
            'isPaid' => $isPaid,
        ]);

        $gatewayMessage = (string) (
            $status['actionCodeDescription']
                ?? $status['ActionCodeDescription']
                ?? $status['ErrorMessage']
                ?? $status['errorMessage']
                ?? ''
        );

        Log::info($isPaid ? 'ClickToPay payment PAID' : 'ClickToPay payment FAILED', [
            'appointment_id' => $appointment->id,
            'orderId' => $orderId,
            'orderNumber' => $gatewayOrderNumber !== '' ? $gatewayOrderNumber : null,
            'test_guess' => $testGuess,
            'orderStatus' => $orderStatusStr,
            'actionCode' => $actionCodeStr,
            'errorCode' => $errorCodeStr,
            'message' => $gatewayMessage !== '' ? $gatewayMessage : null,
        ]);

        // Ensure the returned mdOrder matches the pending payment we created for THIS appointment.
        $pendingPayment = Payment::query()
            ->where('appointment_id', $appointment->id)
            ->where('provider', 'clictopay')
            ->latest('id')
            ->first();

        if ($pendingPayment && $pendingPayment->transaction_id && (string) $pendingPayment->transaction_id !== (string) $orderId) {
            return redirect()->route('patient.appointments')->withErrors([
                'payment' => 'Payment reference mismatch. Please retry the payment from the appointment page.',
            ]);
        }

        if (! $isPaid) {
            $msg = (string) (
                $status['actionCodeDescription']
                    ?? $status['ErrorMessage']
                    ?? $status['errorMessage']
                    ?? ($actionCodeStr === null ? 'Payment not completed (missing action code).' : 'Payment not completed.')
            );

            if ($pendingPayment && strtolower((string) $pendingPayment->status) === 'pending') {
                $pendingPayment->update([
                    'status' => 'failed',
                    'failure_reason' => $msg,
                ]);
            }

            return redirect()->route('patient.appointments')->withErrors([
                'payment' => $msg,
            ]);
        }

        DB::transaction(function () use ($appointment, $orderId, $user) {
            if (strtolower((string) $appointment->status) === 'pending') {
                $appointment->update(['status' => 'confirmed']);
            }

            AppointmentSession::query()->firstOrCreate(
                ['appointment_id' => $appointment->id],
                [
                    'room_id' => (string) Str::uuid(),
                    'status' => 'active',
                ]
            );

            $payment = Payment::query()
                ->where('appointment_id', $appointment->id)
                ->latest('id')
                ->first();

            $payload = [
                'appointment_id' => $appointment->id,
                'amount' => $appointment->price,
                'currency' => (string) ($appointment->currency ?: 'TND'),
                'provider' => 'clictopay',
                'status' => 'paid',
                'payment_method' => 'card',
                'transaction_id' => $orderId,
                'paid_at' => now(),
            ];

            if ($payment) {
                $payment->update($payload);
                ActivityLogger::log($user->id, $user->role ?? null, 'updated_payment', 'Payment', $payment->id, 'ClickToPay payment marked paid for appointment '.$appointment->id);
            } else {
                $created = Payment::create($payload);
                ActivityLogger::log($user->id, $user->role ?? null, 'created_payment', 'Payment', $created->id, 'ClickToPay payment created and marked paid for appointment '.$appointment->id);
            }
        });

        ActivityLogger::log($user->id, $user->role ?? null, 'confirmed_appointment', 'Appointment', $appointment->id, 'Appointment confirmed after ClickToPay payment');

        return redirect()->route('patient.appointments')->with('status', 'Payment successful. Appointment confirmed.');
    }

    /**
     * ClickToPay redirects the user here on failed payment.
     */
    public function clicToPayFail(Request $request, Appointment $appointment)
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->route('login');
        }

        $orderId = (string) ($request->query('mdOrder') ?: $request->query('orderId') ?: '');

        Log::warning('ClickToPay fail callback', [
            'appointment_id' => $appointment->id,
            'orderId' => $orderId !== '' ? $orderId : null,
            'query' => $request->query(),
        ]);

        $pendingPayment = Payment::query()
            ->where('appointment_id', $appointment->id)
            ->where('provider', 'clictopay')
            ->latest('id')
            ->first();

        if ($pendingPayment && strtolower((string) $pendingPayment->status) === 'pending') {
            // Safety: don't overwrite a different transaction_id.
            $matches = $orderId === '' || ! $pendingPayment->transaction_id || (string) $pendingPayment->transaction_id === $orderId;
            if ($matches) {
                $pendingPayment->update([
                    'status' => 'failed',
                    'failure_reason' => 'Gateway redirected to failUrl'.($orderId !== '' ? (' (orderId: '.$orderId.')') : ''),
                ]);
            }
        }

        return redirect()->route('patient.appointments')->withErrors([
            'payment' => 'Payment failed.',
        ]);
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
            $prevStatus = (string) $appointment->status;

            DB::transaction(function () use ($appointment) {
                $appointment->update(['status' => 'confirmed']);

                // Ensure the related session exists (1:1 per appointment).
                AppointmentSession::query()->firstOrCreate(
                    ['appointment_id' => $appointment->id],
                    [
                        'room_id' => (string) Str::uuid(),
                        'status' => 'active',
                    ]
                );

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
                    ActivityLogger::log($appointment->patient_id, null, 'updated_payment', 'Payment', $payment->id, 'Payment updated on appointment confirmation');
                } else {
                    $created = Payment::create($payload);
                    ActivityLogger::log($appointment->patient_id, 'SYSTEM', 'created_payment', 'Payment', $created->id, 'Payment created on appointment confirmation');
                }
            });

            ActivityLogger::log($user->id, $user->role ?? null, 'confirmed_appointment', 'Appointment', $appointment->id, 'Appointment status changed from '.$prevStatus.' to confirmed (patient confirmed and paid)');

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

            $prevStatus = (string) $appointment->status;

            $appointment->update([
                'status' => 'cancelled',
                'canceled_by' => $canceledBy,
                'canceled_by_user_id' => $user->id,
                'cancellation_reason' => $validated['cancellation_reason'] ?? null,
                'canceled_at' => now(),
            ]);
            ActivityLogger::log($user->id, $user->role ?? null, 'cancelled_appointment', 'Appointment', $appointment->id, 'Appointment status changed from '.$prevStatus.' to cancelled by '.$canceledBy);
        } else {
            $prevStatus = (string) $appointment->status;
            $appointment->update(['status' => $requestedStatus]);
            ActivityLogger::log($user->id, $user->role ?? null, 'updated_appointment_status', 'Appointment', $appointment->id, 'Appointment status changed from '.$prevStatus.' to '.$requestedStatus);
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

        ActivityLogger::log($user->id, $user->role ?? null, 'cancelled_appointment', 'Appointment', $appointment->id, 'Appointment cancelled: '.$reason);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Appointment cancelled']);
        }

        return redirect()->back()->with('status', 'Appointment cancelled successfully.');
    }

    /**
     * Return pending appointments count for the authenticated patient (JSON).
     */
    public function pendingCount(Request $request)
    {
        $user = $request->user();
        if (! $user || ! method_exists($user, 'isPatient') || ! $user->isPatient()) {
            return response()->json(['count' => 0]);
        }

        $count = Appointment::query()
            ->where('patient_id', $user->id)
            ->where('status', 'pending')
            ->count();

        return response()->json(['count' => (int) $count]);
    }

    /**
     * Psychologist starts the video call.
     * This ensures the session exists and redirects to the call page.
     * Session "started_at" is now set when BOTH participants actually join the room.
     */
    public function startVideoCall(Request $request, Appointment $appointment)
    {
        $user = $request->user();
        if (! $user || ! method_exists($user, 'isPsychologist') || ! $user->isPsychologist()) {
            abort(403);
        }

        if ((int) $appointment->psychologist_id !== (int) $user->id) {
            abort(403);
        }

        if (strtolower((string) $appointment->status) !== 'confirmed') {
            return redirect()->back()->with('error', 'Video call can only be started for confirmed appointments.');
        }

        $session = null;
        DB::transaction(function () use ($appointment, &$session) {
            $session = AppointmentSession::query()->firstOrCreate(
                ['appointment_id' => $appointment->id],
                [
                    'room_id' => (string) Str::uuid(),
                    'status' => 'active',
                ]
            );
        });

        if ($session) {
            ActivityLogger::log($user->id, $user->role ?? null, 'started_video_call', 'AppointmentSession', $session->id ?? null, 'Psychologist started video call for appointment '.$appointment->id);
        }

        return redirect()->route('appointments.video_call.show', $appointment);
    }

    /**
     * Video call page.
     * Both participants can join; the session starts (started_at) when both join the room.
     */
    public function showVideoCall(Request $request, Appointment $appointment): Response|RedirectResponse
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->route('dashboard');
        }

        $isParticipant = (
            (int) $appointment->patient_id === (int) $user->id ||
            (int) $appointment->psychologist_id === (int) $user->id
        );

        $isAdmin = method_exists($user, 'isAdmin') && $user->isAdmin();

        if (! $isParticipant && ! $isAdmin) {
            abort(403);
        }

        if (strtolower((string) $appointment->status) !== 'confirmed') {
            return redirect()->back()->with('error', 'Video call can only be joined for confirmed appointments.');
        }

        $session = AppointmentSession::query()->firstOrCreate(
            ['appointment_id' => $appointment->id],
            [
                'room_id' => (string) \Illuminate\Support\Str::uuid(),
                'status' => 'active',
            ]
        );

        if ($session) {
            ActivityLogger::log($user->id, $user->role ?? null, 'joined_video_call', 'AppointmentSession', $session->id, 'User joined video call for appointment '.$appointment->id);
        }

        $signalingUrl = (string) (config('app.signaling_url') ?: env('VITE_SIGNALING_URL', 'ws://localhost:3001'));

        $payload = [
            'appointmentId' => $appointment->id,
            'roomId' => (string) $session->room_id,
            'displayName' => (string) ($user->name ?? 'User'),
            'signalingUrl' => $signalingUrl,
        ];

        if (method_exists($user, 'isPsychologist') && $user->isPsychologist()) {
            $payload['role'] = 'psychologist';

            return Inertia::render('Psychologist/Appointments/VideoCall', $payload);
        }

        $payload['role'] = 'patient';

        return Inertia::render('Patient/Appointments/VideoCall', array_merge($payload, [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'authUser' => $user,
            'status' => session('status'),
        ]));
    }
}
