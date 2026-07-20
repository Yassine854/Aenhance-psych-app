<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        // Load relationships if user exists
        if ($user) {
            $user->load(['psychologistProfile', 'patientProfile']);
        }

        // Derive a globally available avatar URL
        $profileImageUrl = null;
        if ($user) {
            if (method_exists($user, 'isPsychologist') && $user->isPsychologist()) {
                $profileImageUrl = optional($user->psychologistProfile)->profile_image_url;
            } elseif (method_exists($user, 'isPatient') && $user->isPatient()) {
                $profileImageUrl = optional($user->patientProfile)->profile_image_url;
            }
        }

        // compute pending appointments for patient users so client can persist it
        $pendingCount = 0;
        if ($user && method_exists($user, 'isPatient') && $user->isPatient()) {
            try {
                $pendingCount = $user->patientAppointments()->where('status', 'pending')->count();
            } catch (\Throwable $e) {
                $pendingCount = 0;
            }
        }

        // Normalize flash payload: if the server stored a translation-like string
        // (e.g. 'appointments.payment_success_confirmed' or a wrapped '## key'),
        // expose it as `status_key` so the client i18n can translate it reliably.
        $status = $request->session()->get('status');
        $statusKey = $request->session()->get('status_key');
        $error = $request->session()->get('error');
        $errorKey = $request->session()->get('error_key');

        $normalizePossibleKey = function ($value) {
            if (! is_string($value) || $value === '') return [null, $value];

            // Trim common wrapper like '## key'
            if (str_starts_with($value, '## ')) {
                $value = trim(substr($value, 3));
            }

            // Very small heuristic: looks like namespace.key or namespace.sub.key
            if (preg_match('/^[a-z0-9_]+\.[a-z0-9_\.]+$/i', $value)) {
                return [$value, null];
            }

            return [null, $value];
        };

        if (! $statusKey) {
            [$maybeKey, $fallback] = $normalizePossibleKey($status);
            if ($maybeKey) {
                $statusKey = $maybeKey;
                $status = $fallback;
            }
        }

        if (! $errorKey) {
            [$maybeKey, $fallbackErr] = $normalizePossibleKey($error);
            if ($maybeKey) {
                $errorKey = $maybeKey;
                $error = $fallbackErr;
            }
        }

        return [
            ...parent::share($request),
            'flash' => [
                'status' => $status,
                'status_key' => $statusKey,
                'error' => $error,
                'error_key' => $errorKey,
            ],
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'profile_image_url' => $profileImageUrl,
                    'psychologistProfile' => $user->psychologistProfile,
                    'patientProfile' => $user->patientProfile,
                    'pending_appointments_count' => $pendingCount,
                ] : null,
            ],
            // Also expose a simple profile object for flexible front-end usage
            'profile' => $profileImageUrl ? [
                'profile_image_url' => $profileImageUrl,
            ] : null,
        ];
    }
}
