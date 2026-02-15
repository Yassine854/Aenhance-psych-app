<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SessionRating;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RatesController extends Controller
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
        $createdFrom = trim((string) $request->input('created_from', ''));
        $createdTo = trim((string) $request->input('created_to', ''));

        $ratingsQuery = SessionRating::query()
            ->with([
                'patient:id,name',
                'psychologist:id,name',
                'session:id,appointment_id,started_at,ended_at',
                'session.appointment:id,patient_id,psychologist_id,scheduled_start,scheduled_end,status',
            ]);

        if ($createdFrom !== '') {
            $ratingsQuery->whereDate('created_at', '>=', $createdFrom);
        }

        if ($createdTo !== '') {
            $ratingsQuery->whereDate('created_at', '<=', $createdTo);
        }

        if ($searchQuery !== '') {
            if ($searchField === 'patient') {
                $ratingsQuery->whereHas('patient', function ($q) use ($searchQuery) {
                    $q->where('name', 'like', '%'.$searchQuery.'%');
                });
            } elseif ($searchField === 'psychologist') {
                $ratingsQuery->whereHas('psychologist', function ($q) use ($searchQuery) {
                    $q->where('name', 'like', '%'.$searchQuery.'%');
                });
            } elseif ($searchField === 'rating') {
                $ratingsQuery->where('rating', 'like', '%'.$searchQuery.'%');
            } else {
                $ratingsQuery->where('id', 'like', '%'.$searchQuery.'%');
            }
        }

        $ratings = $ratingsQuery->orderByDesc('id')->paginate(15)->appends($request->query());

        $mappedItems = collect($ratings->items())->map(function (SessionRating $r) {
            return [
                'id' => $r->id,
                'rating' => (int) $r->rating,
                'comment' => $r->comment,
                'created_at' => optional($r->created_at)->toISOString() ?? (string) $r->created_at,
                'patient' => $r->patient ? [
                    'id' => $r->patient->id,
                    'name' => $r->patient->name,
                ] : null,
                'psychologist' => $r->psychologist ? [
                    'id' => $r->psychologist->id,
                    'name' => $r->psychologist->name,
                ] : null,
                'session' => $r->session ? [
                    'id' => $r->session->id,
                    'appointment_id' => $r->session->appointment_id,
                    'started_at' => optional($r->session->started_at)->toISOString() ?? ($r->session->started_at ? (string) $r->session->started_at : null),
                    'ended_at' => optional($r->session->ended_at)->toISOString() ?? ($r->session->ended_at ? (string) $r->session->ended_at : null),
                ] : null,
                'appointment' => ($r->session && $r->session->appointment) ? [
                    'id' => $r->session->appointment->id,
                    'status' => (string) $r->session->appointment->status,
                    'scheduled_start' => optional($r->session->appointment->scheduled_start)->toISOString() ?? ($r->session->appointment->scheduled_start ? (string) $r->session->appointment->scheduled_start : null),
                    'scheduled_end' => optional($r->session->appointment->scheduled_end)->toISOString() ?? ($r->session->appointment->scheduled_end ? (string) $r->session->appointment->scheduled_end : null),
                ] : null,
            ];
        })->values()->all();

        $ratings = new \Illuminate\Pagination\LengthAwarePaginator(
            $mappedItems,
            $ratings->total(),
            $ratings->perPage(),
            $ratings->currentPage(),
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return Inertia::render('Admin/Rates/Index', [
            'ratings' => $ratings,
            'status' => session('status'),
            'filters' => [
                'search_field' => in_array($searchField, ['id', 'patient', 'psychologist', 'rating'], true) ? $searchField : 'id',
                'search_query' => $searchQuery,
                'created_from' => $createdFrom,
                'created_to' => $createdTo,
            ],
        ]);
    }

    public function show(Request $request, SessionRating $rating)
    {
        $rating->load([
            'patient:id,name',
            'psychologist:id,name',
            'session:id,appointment_id,started_at,ended_at',
            'session.appointment:id,patient_id,psychologist_id,scheduled_start,scheduled_end,status',
        ]);

        $payload = [
            'id' => $rating->id,
            'rating' => (int) $rating->rating,
            'comment' => $rating->comment,
            'created_at' => optional($rating->created_at)->toISOString() ?? (string) $rating->created_at,
            'patient' => $rating->patient ? [
                'id' => $rating->patient->id,
                'name' => $rating->patient->name,
            ] : null,
            'psychologist' => $rating->psychologist ? [
                'id' => $rating->psychologist->id,
                'name' => $rating->psychologist->name,
            ] : null,
            'session' => $rating->session ? [
                'id' => $rating->session->id,
                'appointment_id' => $rating->session->appointment_id,
                'started_at' => optional($rating->session->started_at)->toISOString() ?? ($rating->session->started_at ? (string) $rating->session->started_at : null),
                'ended_at' => optional($rating->session->ended_at)->toISOString() ?? ($rating->session->ended_at ? (string) $rating->session->ended_at : null),
            ] : null,
            'appointment' => ($rating->session && $rating->session->appointment) ? [
                'id' => $rating->session->appointment->id,
                'status' => (string) $rating->session->appointment->status,
                'scheduled_start' => optional($rating->session->appointment->scheduled_start)->toISOString() ?? ($rating->session->appointment->scheduled_start ? (string) $rating->session->appointment->scheduled_start : null),
                'scheduled_end' => optional($rating->session->appointment->scheduled_end)->toISOString() ?? ($rating->session->appointment->scheduled_end ? (string) $rating->session->appointment->scheduled_end : null),
            ] : null,
        ];

        return Inertia::render('Admin/Rates/Show', [
            'rating' => $payload,
        ]);
    }
}
