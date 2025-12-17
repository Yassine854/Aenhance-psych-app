<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AppointmentController extends Controller
{
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

    // Create appointment (Patient)
    public function store(Request $request)
    {
        $request->validate([
            'psychologist_id' => 'required|exists:users,id',
            'appointment_date' => 'required|date',
        ]);

        return Appointment::create([
            'patient_id' => Auth::id(),
            'psychologist_id' => $request->psychologist_id,
            'appointment_date' => $request->appointment_date,
        ]);
    }

    // Update appointment status (Psychologist/Admin)
    public function update(Request $request, Appointment $appointment)
    {
        $request->validate([
            'status' => 'required|in:scheduled,completed,cancelled',
        ]);

        $appointment->update(['status' => $request->status]);

        return $appointment;
    }

    // Cancel appointment (Patient/Admin)
    public function destroy(Appointment $appointment)
    {
        $appointment->delete();
        return response()->json(['message' => 'Appointment deleted']);
    }
}
