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

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'profile_image_url' => $profileImageUrl,
                    'psychologistProfile' => $user->psychologistProfile,
                    'patientProfile' => $user->patientProfile,
                ] : null,
            ],
            // Also expose a simple profile object for flexible front-end usage
            'profile' => $profileImageUrl ? [
                'profile_image_url' => $profileImageUrl,
            ] : null,
        ];
    }
}
