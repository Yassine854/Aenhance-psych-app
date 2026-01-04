<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\PatientProfile;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

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

        if (! empty($data['remove_profile_image'])) {
            $data['profile_image_url'] = null;
        }

        if ($request->hasFile('profile_image')) {
            try {
                $uploaded = Cloudinary::upload($request->file('profile_image')->getRealPath(), [
                    'folder' => 'patient_profiles',
                    'transformation' => ['width' => 800, 'height' => 800, 'crop' => 'limit'],
                ]);

                $url = method_exists($uploaded, 'getSecurePath')
                    ? $uploaded->getSecurePath()
                    : (method_exists($uploaded, 'getPath') ? $uploaded->getPath() : null);

                if ($url) {
                    $data['profile_image_url'] = $url;
                }
            } catch (\Throwable $e) {
                Log::warning('Cloudinary patient profile_image upload failed: '.$e->getMessage());
                $path = $request->file('profile_image')->store('patient_profiles', 'public');
                $data['profile_image_url'] = Storage::url($path);
            }
        }

        unset($data['profile_image']);
        unset($data['remove_profile_image']);

        PatientProfile::updateOrCreate(
            ['user_id' => $user->id],
            array_merge($data, ['user_id' => $user->id])
        );

        return redirect()->route('patient.profile')->with('status', 'Profile updated successfully.');
    }
}
