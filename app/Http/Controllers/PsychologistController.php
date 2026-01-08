<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class PsychologistController extends Controller
{
    // View all appointments for logged-in psychologist
    public function appointments()
    {
        $user = Auth::user();
        if (! $user || ! method_exists($user, 'isPsychologist') || ! $user->isPsychologist()) {
            abort(403);
        }

        return $user->psychologistAppointments;
    }

    // View all patients assigned to psychologist
    public function patients()
    {
        $user = Auth::user();
        if (! $user || ! method_exists($user, 'isPsychologist') || ! $user->isPsychologist()) {
            abort(403);
        }

        return $user
            ->psychologistAppointments()
            ->with('patient')
            ->get()
            ->pluck('patient')
            ->unique('id');
    }
}
