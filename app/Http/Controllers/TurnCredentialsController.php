<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TurnCredentialsController extends Controller
{
    public function __invoke(Request $request, Appointment $appointment): JsonResponse
    {
        $userId = (int) $request->user()->id;
        abort_unless(in_array($userId, [(int) $appointment->patient_id, (int) $appointment->psychologist_id], true), 403);
        abort_unless(strtolower((string) $appointment->status) === 'confirmed', 403);

        $secret = (string) config('turn.secret');
        if ($secret === '') {
            return response()->json([
                'code' => 'turn_not_configured',
                'message' => 'Temporary TURN credentials are not configured on the application server.',
            ], 503)
                ->header('Cache-Control', 'no-store, private');
        }

        $expiresAt = now()->timestamp + (int) config('turn.ttl');
        $username = $expiresAt.':'.$userId;

        return response()->json([
            'iceServers' => [[
                'urls' => config('turn.urls'),
                'username' => $username,
                'credential' => base64_encode(hash_hmac('sha1', $username, $secret, true)),
            ]],
            'expiresAt' => $expiresAt,
        ])->header('Cache-Control', 'no-store, private');
    }
}
