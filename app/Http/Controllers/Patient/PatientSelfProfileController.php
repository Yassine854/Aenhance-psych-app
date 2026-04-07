<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\PatientProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use App\Services\ActivityLogger;

class PatientSelfProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        $user = $request->user();
        if (! $user || ! method_exists($user, 'isPatient') || ! $user->isPatient()) {
            return Inertia::render('Welcome', [
                'canLogin' => Route::has('login'),
                'canRegister' => Route::has('register'),
                'authUser' => $user,
            ]);
        }

        return Inertia::render('Patient/Profile', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'authUser' => $user,
            'profile' => $user->patientProfile,
            'status' => session('status'),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        if (! $user || ! method_exists($user, 'isPatient') || ! $user->isPatient()) {
            return redirect()->route('dashboard');
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date'],
            'gender' => ['nullable', 'string', 'max:50'],
            'country' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'country_code' => ['nullable', 'string', 'max:10'],
            'remove_profile_image' => ['nullable', 'boolean'],
            'profile_image' => ['nullable', 'image', 'max:5120'],
        ]);

        $data = $validated;

        // If user requested removal, delete existing local avatar if present
        if (! empty($data['remove_profile_image'])) {
            $existing = $user->patientProfile;
            if ($existing && ! empty($existing->profile_image_url)) {
                try {
                    $existing->deleteProfileImageFile();
                } catch (\Throwable $e) {
                    Log::warning('Failed to delete existing avatar: '.$e->getMessage());
                }
            }

            $data['profile_image_url'] = null;
        }

        // Handle uploaded profile image: store locally in `public/avatars`
        if ($request->hasFile('profile_image')) {
            // delete previous local avatar if exists
            $existing = $user->patientProfile;
            if ($existing && ! empty($existing->profile_image_url)) {
                try {
                    $existing->deleteProfileImageFile();
                } catch (\Throwable $e) {
                    Log::warning('Failed to delete existing avatar before upload: '.$e->getMessage());
                }
            }

            try {
                $path = $request->file('profile_image')->store('avatars', config('app.avatar_disk', 'public'));
                $data['profile_image_url'] = $path;
            } catch (\Throwable $e) {
                Log::warning('Local patient profile_image upload failed: '.$e->getMessage());
            }
        }

        unset($data['profile_image']);
        unset($data['remove_profile_image']);

        PatientProfile::updateOrCreate(
            ['user_id' => $user->id],
            array_merge($data, ['user_id' => $user->id])
        );

        ActivityLogger::log($user->id, $user->role ?? null, 'updated_profile', 'PatientProfile', $user->patientProfile?->id ?? null, 'Patient updated own profile');

        return redirect()->route('patient.profile')->with('status', 'Profile updated successfully.');
    }
}
