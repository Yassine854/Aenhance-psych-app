<?php

namespace App\Http\Controllers\Psychologist;

use App\Http\Controllers\Controller;
use App\Models\PsychologistPayout;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;

class PsychologistPayoutController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        if (! $user || ! method_exists($user, 'isPsychologist') || ! $user->isPsychologist()) {
            return Inertia::render('Dashboard');
        }

        /** @var LengthAwarePaginator $payouts */
        $payouts = PsychologistPayout::query()
            ->where('psychologist_id', $user->id)
            ->with(['appointment.patient', 'payment'])
            ->orderByDesc('created_at')
            ->paginate(15);

        $payouts->withQueryString();

        $payload = $payouts->through(function (PsychologistPayout $p) {
            return [
                'id' => $p->id,
                'appointment' => $p->appointment ? [
                    'id' => $p->appointment->id,
                    'scheduled_start' => optional($p->appointment->scheduled_start)->toISOString() ?? (string) $p->appointment->scheduled_start,
                ] : null,
                'patient' => $p->appointment && $p->appointment->patient ? [
                    'id' => $p->appointment->patient->id,
                    'name' => $p->appointment->patient->name,
                ] : null,
                'payment_id' => $p->payment_id,
                'gross_amount' => (string) $p->gross_amount,
                'platform_fee' => (string) $p->platform_fee,
                'net_amount' => (string) $p->net_amount,
                'currency' => $p->currency,
                'status' => (string) $p->status,
                'estimated_availability' => optional($p->estimated_availability)->toISOString() ?? ($p->estimated_availability ? (string) $p->estimated_availability : null),
                'paid_at' => optional($p->paid_at)->toISOString() ?? ($p->paid_at ? (string) $p->paid_at : null),
                'created_at' => optional($p->created_at)->toISOString(),
                'refund_at' => optional($p->refund_at)->toISOString()
                ];
        });

        return Inertia::render('Psychologist/Payouts/Index', [
            'payouts' => $payload,
            'status' => session('status'),
        ]);
    }
}
