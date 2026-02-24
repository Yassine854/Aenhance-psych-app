<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    private function chartGranularity(Request $request): string
    {
        $value = strtolower(trim((string) $request->input('granularity', 'months')));

        return in_array($value, ['days', 'months', 'years'], true) ? $value : 'months';
    }

    public function index(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->route('login');
        }

        if (method_exists($user, 'isPsychologist') && $user->isPsychologist()) {
            return redirect()->route('home');
        }

        if (method_exists($user, 'isPatient') && $user->isPatient()) {
            return redirect()->route('home');
        }

        if (! method_exists($user, 'isAdmin') || ! $user->isAdmin()) {
            return redirect()->route('home');
        }

        $totalPatients = (int) User::query()->whereRaw("UPPER(role) = 'PATIENT'")->count();
        $totalPsychologists = (int) User::query()->whereRaw("UPPER(role) = 'PSYCHOLOGIST'")->count();

        $appointmentsTotal = (int) Appointment::query()->count();
        $appointmentsByStatus = Appointment::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $paymentsTotal = (int) Payment::query()->count();
        $paymentsByStatus = Payment::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $paidPayments = (int) ($paymentsByStatus['paid'] ?? 0);
        $pendingPayments = (int) ($paymentsByStatus['pending'] ?? 0);
        $failedPayments = (int) ($paymentsByStatus['failed'] ?? 0);
        $refundedPayments = (int) ($paymentsByStatus['refunded'] ?? 0);

        $totalRevenue = (float) Payment::query()
            ->whereRaw("LOWER(status) = 'paid'")
            ->sum('amount');

        $granularity = $this->chartGranularity($request);

        if ($granularity === 'days') {
            $bucketDates = collect(range(29, 0))->map(fn ($i) => Carbon::now()->subDays($i)->startOfDay());
            $bucketKeys = $bucketDates->map(fn (Carbon $d) => $d->format('Y-m-d'))->values();
            $periodLabels = $bucketDates->map(fn (Carbon $d) => $d->format('d M'))->values();
            $rangeStart = $bucketDates->first()->copy()->startOfDay();
            $rangeEnd = $bucketDates->last()->copy()->endOfDay();
            $groupFormat = '%Y-%m-%d';
        } elseif ($granularity === 'years') {
            $bucketDates = collect(range(4, 0))->map(fn ($i) => Carbon::now()->subYears($i)->startOfYear());
            $bucketKeys = $bucketDates->map(fn (Carbon $d) => $d->format('Y'))->values();
            $periodLabels = $bucketDates->map(fn (Carbon $d) => $d->format('Y'))->values();
            $rangeStart = $bucketDates->first()->copy()->startOfYear();
            $rangeEnd = $bucketDates->last()->copy()->endOfYear();
            $groupFormat = '%Y';
        } else {
            $bucketDates = collect(range(11, 0))->map(fn ($i) => Carbon::now()->subMonths($i)->startOfMonth());
            $bucketKeys = $bucketDates->map(fn (Carbon $d) => $d->format('Y-m'))->values();
            $periodLabels = $bucketDates->map(fn (Carbon $d) => $d->format('M Y'))->values();
            $rangeStart = $bucketDates->first()->copy()->startOfMonth();
            $rangeEnd = $bucketDates->last()->copy()->endOfMonth();
            $groupFormat = '%Y-%m';
        }

        $periodAppointmentsTotal = (int) Appointment::query()
            ->whereBetween('scheduled_start', [$rangeStart, $rangeEnd])
            ->count();

        $periodCompletedAppointments = (int) Appointment::query()
            ->whereBetween('scheduled_start', [$rangeStart, $rangeEnd])
            ->whereRaw("LOWER(status) = 'completed'")
            ->count();

        $appointmentCompletionRate = $periodAppointmentsTotal > 0
            ? round(($periodCompletedAppointments / $periodAppointmentsTotal) * 100, 1)
            : 0.0;

        $periodPaymentsTotal = (int) Payment::query()
            ->whereBetween(DB::raw('COALESCE(paid_at, created_at)'), [$rangeStart, $rangeEnd])
            ->count();

        $periodPaidPayments = (int) Payment::query()
            ->whereBetween(DB::raw('COALESCE(paid_at, created_at)'), [$rangeStart, $rangeEnd])
            ->whereRaw("LOWER(status) = 'paid'")
            ->count();

        $paymentSuccessRate = $periodPaymentsTotal > 0
            ? round(($periodPaidPayments / $periodPaymentsTotal) * 100, 1)
            : 0.0;

        $appointmentsRaw = Appointment::query()
            ->selectRaw("DATE_FORMAT(created_at, '{$groupFormat}') as bucket, COUNT(*) as total")
            ->whereBetween('created_at', [$rangeStart, $rangeEnd])
            ->groupBy('bucket')
            ->pluck('total', 'bucket');

        $revenueRaw = Payment::query()
            ->selectRaw("DATE_FORMAT(COALESCE(paid_at, created_at), '{$groupFormat}') as bucket, SUM(amount) as total")
            ->whereRaw("LOWER(status) = 'paid'")
            ->whereBetween(DB::raw('COALESCE(paid_at, created_at)'), [$rangeStart, $rangeEnd])
            ->groupBy('bucket')
            ->pluck('total', 'bucket');

        $paymentsRaw = Payment::query()
            ->selectRaw("DATE_FORMAT(created_at, '{$groupFormat}') as bucket, COUNT(*) as total")
            ->whereBetween('created_at', [$rangeStart, $rangeEnd])
            ->groupBy('bucket')
            ->pluck('total', 'bucket');

        $usersRaw = User::query()
            ->selectRaw("DATE_FORMAT(created_at, '{$groupFormat}') as bucket")
            ->selectRaw("SUM(CASE WHEN UPPER(role) = 'PATIENT' THEN 1 ELSE 0 END) as patients")
            ->selectRaw("SUM(CASE WHEN UPPER(role) = 'PSYCHOLOGIST' THEN 1 ELSE 0 END) as psychologists")
            ->whereBetween('created_at', [$rangeStart, $rangeEnd])
            ->groupBy('bucket')
            ->get()
            ->keyBy('bucket');

        $timelineAppointments = $bucketKeys->map(fn ($key) => (int) ($appointmentsRaw[$key] ?? 0))->values();
        $timelineRevenue = $bucketKeys->map(fn ($key) => round((float) ($revenueRaw[$key] ?? 0), 2))->values();
        $timelinePayments = $bucketKeys->map(fn ($key) => (int) ($paymentsRaw[$key] ?? 0))->values();

        $timelinePatients = $bucketKeys->map(function ($key) use ($usersRaw) {
            $row = $usersRaw->get($key);

            return (int) ($row->patients ?? 0);
        })->values();

        $timelinePsychologists = $bucketKeys->map(function ($key) use ($usersRaw) {
            $row = $usersRaw->get($key);

            return (int) ($row->psychologists ?? 0);
        })->values();

        return Inertia::render('Admin/Dashboard', [
            'kpis' => [
                'users_patients' => $totalPatients,
                'users_psychologists' => $totalPsychologists,
                'appointments_total' => $appointmentsTotal,
                'payments_total' => $paymentsTotal,
                'payments_paid' => $paidPayments,
                'payments_pending' => $pendingPayments,
                'payments_failed' => $failedPayments,
                'payments_refunded' => $refundedPayments,
                'revenue_total' => round($totalRevenue, 2),
                'payment_success_rate' => $paymentSuccessRate,
                'appointment_completion_rate' => $appointmentCompletionRate,
            ],
            'chart_filter' => [
                'granularity' => $granularity,
            ],
            'charts' => [
                'period_labels' => $periodLabels,
                'timeline_revenue' => $timelineRevenue,
                'timeline_appointments' => $timelineAppointments,
                'timeline_payments' => $timelinePayments,
                'timeline_new_patients' => $timelinePatients,
                'timeline_new_psychologists' => $timelinePsychologists,
                'payment_status' => [
                    'labels' => ['Paid', 'Pending', 'Failed', 'Refunded'],
                    'series' => [$paidPayments, $pendingPayments, $failedPayments, $refundedPayments],
                ],
                'appointment_status' => [
                    'labels' => ['Pending', 'Confirmed', 'Completed', 'Cancelled', 'No show'],
                    'series' => [
                        (int) ($appointmentsByStatus['pending'] ?? 0),
                        (int) ($appointmentsByStatus['confirmed'] ?? 0),
                        (int) ($appointmentsByStatus['completed'] ?? 0),
                        (int) ($appointmentsByStatus['cancelled'] ?? 0),
                        (int) ($appointmentsByStatus['no_show'] ?? 0),
                    ],
                ],
            ],
        ]);
    }
}
