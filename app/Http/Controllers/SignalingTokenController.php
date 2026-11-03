<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SignalingTokenController extends Controller
{
    public function __invoke(Request $request, Appointment $appointment): JsonResponse
    {
        $user = $request->user();
        $role = match (true) {
            $user->isPsychologist() && (int) $appointment->psychologist_id === (int) $user->id => 'psychologist',
            $user->isPatient() && (int) $appointment->patient_id === (int) $user->id => 'patient',
            default => null,
        };
        abort_unless($role && strtolower((string) $appointment->status) === 'confirmed', 403);
        $session = AppointmentSession::where('appointment_id', $appointment->id)->firstOrFail();
        abort_if($session->ended_at || $session->status === 'completed', 403);

        $secret = (string) config('signaling.secret');
        abort_if(strlen($secret) < 32, 503, 'Signaling authentication is not configured.');
        $payload = rtrim(strtr(base64_encode(json_encode([
            'roomId' => (string) $session->room_id,
            'role' => $role,
            'name' => (string) $user->name,
            'exp' => now()->timestamp + 300,
        ], JSON_THROW_ON_ERROR)), '+/', '-_'), '=');

        return response()->json(['token' => $payload.'.'.hash_hmac('sha256', $payload, $secret)])
            ->header('Cache-Control', 'no-store, private');
    }
}
