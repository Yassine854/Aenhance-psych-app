<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PsychologistController extends Controller
{
    // View all appointments for logged-in psychologist
    public function appointments()
    {
        return Auth::user()->psychologistAppointments;
    }

    // View all patients assigned to psychologist
    public function patients()
    {
        return Auth::user()
            ->psychologistAppointments()
            ->with('patient')
            ->get()
            ->pluck('patient')
            ->unique('id');
    }
}
