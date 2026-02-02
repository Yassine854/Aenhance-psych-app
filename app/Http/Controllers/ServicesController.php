<?php

namespace App\Http\Controllers;

use App\Models\PsychologistProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class ServicesController extends Controller
{
    public function index()
    {
        return Inertia::render('guest/services/Index', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'authUser' => Auth::user(),
        ]);
    }

    public function consultation()
    {
        $profiles = PsychologistProfile::query()
            ->with(['user', 'availabilities', 'specialisations', 'expertises'])
            ->where('is_approved', true)
            ->whereHas('user', function ($query) {
                $query->where('role', 'PSYCHOLOGIST')
                    ->where('is_active', 1);
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get()
            ->map(function (PsychologistProfile $profile) {
                $languages = $profile->languages;
                if (is_string($languages) && $languages !== '') {
                    $decoded = json_decode($languages, true);
                    $languages = (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : [];
                }
                if (! is_array($languages)) {
                    $languages = [];
                }

                // compute rating average and count from session_ratings (tied to user id)
                $rating = DB::table('session_ratings')
                    ->where('psychologist_id', $profile->user_id)
                    ->selectRaw('AVG(rating) as avg, COUNT(*) as count')
                    ->first();

                return [
                    'id' => $profile->id,
                    'first_name' => $profile->first_name,
                    'last_name' => $profile->last_name,
                    'specialisations' => $profile->specialisations
                        ->sortBy('name')
                        ->values()
                        ->map(fn ($s) => ['id' => $s->id, 'name' => $s->name]),
                    'expertises' => $profile->expertises
                        ->sortBy('name')
                        ->values()
                        ->map(fn ($s) => ['id' => $s->id, 'name' => $s->name]),
                    'languages' => array_values(array_unique(array_filter($languages))),
                    'bio' => $profile->bio,
                    'price_per_session' => $profile->price_per_session,
                    'profile_image_url' => $profile->profile_image_url,
                    'is_approved' => (bool) $profile->is_approved,
                    'user' => $profile->user ? [
                        'id' => $profile->user->id,
                        'name' => $profile->user->name,
                    ] : null,
                    'availabilities' => $profile->availabilities
                        ->sortBy(['day_of_week', 'start_time'])
                        ->values()
                        ->map(function ($availability) {
                            return [
                                'day_of_week' => (int) $availability->day_of_week,
                                'start_time' => (string) $availability->start_time,
                                'end_time' => (string) $availability->end_time,
                            ];
                        }),
                    // rating fields: average (decimal) and count (int)
                    'rating_average' => $rating->avg !== null ? round((float)$rating->avg, 2) : null,
                    'ratings_count' => $rating->count ?? 0,
                ];
            })
            ->values();

        return Inertia::render('guest/services/Consultation', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'authUser' => Auth::user(),
            'profiles' => $profiles,
        ]);
    }
}
