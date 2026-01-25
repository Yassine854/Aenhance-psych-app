<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSessionRatingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class SessionRatingController extends Controller
{
    public function store(StoreSessionRatingRequest $request)
    {
        $user = $request->user();

        $session = DB::table('appointment_sessions')->where('id', $request->input('session_id'))->first();
        if (!$session) {
            return response()->json(['message' => 'Session not found'], 422);
        }

        // Ensure session was ended/completed
        if (empty($session->ended_at)) {
            return response()->json(['message' => 'Session not completed'], 422);
        }

        // Derive appointment from the session record (no appointment_id required in request)
        $appointment = DB::table('appointments')->where('id', $session->appointment_id)->first();
        if (!$appointment) {
            return response()->json(['message' => 'Appointment not found for session'], 422);
        }

        // Only the patient attached to the appointment can rate
        if ((int) $appointment->patient_id !== (int) $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Enforce one rating per session
        $exists = DB::table('session_ratings')->where('session_id', $session->id)->exists();
        if ($exists) {
            return response()->json(['message' => 'Session already rated'], 409);
        }

        try {
            $now = Carbon::now();
            $id = DB::table('session_ratings')->insertGetId([
                'session_id' => $session->id,
                'patient_id' => $user->id,
                'psychologist_id' => $appointment->psychologist_id ?? null,
                'rating' => (int) $request->input('rating'),
                'comment' => $request->input('comment') ?: null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $record = DB::table('session_ratings')->where('id', $id)->first();
            return response()->json($record, 201);
        } catch (\Exception $e) {
            logger()->error('Failed storing session rating: '.$e->getMessage());
            return response()->json(['message' => 'Could not store rating'], 500);
        }
    }
}
