<?php

namespace App\Http\Controllers;

use App\Http\Requests\PatientProfileRequest;
use App\Models\PatientProfile;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use App\Services\ActivityLogger;

class PatientProfileController extends Controller
{
    public function index(Request $request)
    {
        $profiles = PatientProfile::query()
            ->with(['user' => function ($query) {
                $query->where('role', 'PATIENT');
            }])
            ->whereHas('user', function ($query) {
                $query->where('role', 'PATIENT');
            })
            ->paginate(15);

        if ($request->wantsJson()) {
            return response()->json($profiles);
        }

        return Inertia::render('Admin/Patient/Index', [
            'profiles' => $profiles,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Patient/Create', [
            'show' => true,
        ]);
    }

    public function store(PatientProfileRequest $request): HttpResponse
    {
        $data = $request->validated();

        $userToCreate = null;
        if ($request->has('new_user_name') && $request->has('new_user_email') && $request->has('new_user_password')) {
            $userToCreate = [
                'name' => $request->input('new_user_name'),
                'email' => $request->input('new_user_email'),
                'password' => $request->input('new_user_password'),
                'role' => 'PATIENT',
            ];
        }

        \DB::beginTransaction();

        try {
            if ($userToCreate) {
                $validated = validator($userToCreate, [
                    'name' => 'required|string|max:255',
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required|min:6',
                    'role' => 'required|in:ADMIN,PSYCHOLOGIST,PATIENT',
                ])->validate();

                $user = \App\Models\User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => \Hash::make($validated['password']),
                    'role' => $validated['role'],
                ]);

                $data['user_id'] = $user->id;
            } elseif (empty($data['user_id']) && $request->user()) {
                $data['user_id'] = $request->user()->id;
            }

            if ($request->hasFile('profile_image')) {
                try {
                    $uploaded = Cloudinary::upload($request->file('profile_image')->getRealPath(), [
                        'folder' => 'patient_profiles',
                        'transformation' => ['width' => 800, 'height' => 800, 'crop' => 'limit'],
                    ]);
                    $url = method_exists($uploaded, 'getSecurePath') ? $uploaded->getSecurePath() : (method_exists($uploaded, 'getPath') ? $uploaded->getPath() : null);
                    if ($url) {
                        $data['profile_image_url'] = $url;
                    }
                } catch (\Throwable $e) {
                    Log::warning('Cloudinary profile_image upload failed: '.$e->getMessage());
                    $path = $request->file('profile_image')->store('patient_profiles', 'public');
                    $data['profile_image_url'] = Storage::url($path);
                }
            }

            unset($data['profile_image']);

            $profile = PatientProfile::create($data);
            ActivityLogger::log($request->user()->id ?? $data['user_id'] ?? null, 'ADMIN', 'created_patient_profile', 'PatientProfile', $profile->id, 'Created patient profile');

            \DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'profile' => $profile->fresh()->load('user'),
                ], 201);
            }

            return redirect()->route('patient-profiles.index');
        } catch (ValidationException $e) {
            \DB::rollBack();

            if ($request->expectsJson()) {
                return response()->json(['errors' => $e->errors()], 422);
            }

            return back()->withErrors($e->errors())->withInput();
        } catch (QueryException $e) {
            \DB::rollBack();

            Log::error('Database error creating patient', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            if ($e->getCode() == 23000 || str_contains($e->getMessage(), 'Duplicate entry')) {
                $errors = [];

                if (str_contains($e->getMessage(), 'users_email_unique') || str_contains($e->getMessage(), 'email')) {
                    $errors['new_user_email'] = ['This email is already registered'];
                } else {
                    $errors['general'] = ['This record already exists'];
                }

                if ($request->expectsJson()) {
                    return response()->json(['errors' => $errors], 422);
                }

                return back()->withErrors($errors)->withInput();
            }

            $fallbackErrors = ['general' => ['Database error: Unable to create patient']];
            if ($request->expectsJson()) {
                return response()->json(['errors' => $fallbackErrors], 500);
            }

            return back()->withErrors($fallbackErrors)->withInput();
        } catch (\Throwable $e) {
            \DB::rollBack();

            Log::error('Unexpected error creating patient', [
                'error' => $e->getMessage(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['errors' => ['general' => ['Unable to create patient']]], 500);
            }

            return back()->withErrors(['general' => 'Unable to create patient'])->withInput();
        }
    }

    public function show(PatientProfile $patientProfile): Response
    {
        return Inertia::render('Admin/Patient/Show', [
            'show' => true,
            'patient' => $patientProfile->load('user'),
        ]);
    }

    public function edit(PatientProfile $patientProfile): Response
    {
        return Inertia::render('Admin/Patient/Edit', [
            'show' => true,
            'patient' => $patientProfile->load('user'),
        ]);
    }

    public function update(PatientProfileRequest $request, PatientProfile $patientProfile)
    {
        $data = $request->validated();

        if ($request->hasFile('profile_image')) {
            try {
                $uploaded = Cloudinary::upload($request->file('profile_image')->getRealPath(), [
                    'folder' => 'patient_profiles',
                    'transformation' => ['width' => 800, 'height' => 800, 'crop' => 'limit'],
                ]);
                $url = method_exists($uploaded, 'getSecurePath') ? $uploaded->getSecurePath() : (method_exists($uploaded, 'getPath') ? $uploaded->getPath() : null);
                if ($url) {
                    $data['profile_image_url'] = $url;
                }
            } catch (\Throwable $e) {
                Log::warning('Cloudinary profile_image upload failed: '.$e->getMessage());
                $path = $request->file('profile_image')->store('patient_profiles', 'public');
                $data['profile_image_url'] = Storage::url($path);
            }
        }

        unset($data['profile_image']);

        $patientProfile->update($data);
        ActivityLogger::log($request->user()->id ?? null, 'ADMIN', 'updated_patient_profile', 'PatientProfile', $patientProfile->id, 'Updated patient profile');

        if ($request->expectsJson()) {
            return response()->json([
                'profile' => $patientProfile->fresh()->load('user'),
            ]);
        }

        if ($request->header('X-Inertia')) {
            return response()->noContent();
        }

        return redirect()->route('patient-profiles.index');
    }

    public function destroy(PatientProfile $patientProfile)
    {
        $patientProfile->delete();
        ActivityLogger::log(request()->user()->id ?? null, 'ADMIN', 'deleted_patient_profile', 'PatientProfile', $patientProfile->id, 'Deleted patient profile');

        if (request()->expectsJson()) {
            return response()->noContent();
        }

        return redirect()->route('patient-profiles.index');
    }
}
