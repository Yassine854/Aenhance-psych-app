<?php

namespace App\Http\Controllers;

use App\Http\Requests\PsychologistProfileRequest;
use App\Models\PsychologistProfile;
use App\Models\Specialisation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class PsychologistProfileController extends Controller
{
    public function approve(Request $request, PsychologistProfile $psychologistProfile): HttpResponse
    {
        $psychologistProfile->is_approved = true;
        $psychologistProfile->save();

        if ($request->expectsJson()) {
            return response()->noContent();
        }

        return redirect()->back();
    }

    public function index(Request $request)
    {
        // Load psychologist profiles with their users efficiently
        $profiles = PsychologistProfile::with([
            'user' => function ($query) {
            $query->where('role', 'PSYCHOLOGIST');
            },
            'availabilities',
            'specialisations',
        ])->whereHas('user', function ($query) {
            $query->where('role', 'PSYCHOLOGIST');
        })->paginate(15);

        if ($request->wantsJson()) {
            return response()->json($profiles);
        }

        return Inertia::render('Admin/Psychologist/Index', [
            'profiles' => $profiles,
            'specialisations' => Specialisation::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    // Local-only helper to return JSON without auth (for Postman testing)
    public function testJson()
    {
        if (!app()->environment('local')) {
            abort(404);
        }

        return response()->json(PsychologistProfile::with(['user', 'availabilities'])->get());
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Psychologist/Create', [
            'specialisations' => Specialisation::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(PsychologistProfileRequest $request): HttpResponse
    {
        Log::info('Store method called', [
            'has_new_user_name' => $request->has('new_user_name'),
            'has_diploma_file' => $request->hasFile('diploma_file'),
            'has_cin_file' => $request->hasFile('cin_file'),
        ]);
        
        $data = $request->validated();

        $specialisationIds = [];
        if (array_key_exists('specialisation_ids', $data)) {
            $specialisationIds = is_array($data['specialisation_ids']) ? $data['specialisation_ids'] : [];
            unset($data['specialisation_ids']);
        }

        $availabilities = [];
        if (isset($data['availabilities']) && is_array($data['availabilities'])) {
            $availabilities = $data['availabilities'];
        }
        unset($data['availabilities']);
        
        Log::info('Data after validation', ['data_keys' => array_keys($data)]);
        
        // Check if we need to create a user first (from admin create form)
        $userToCreate = null;
        if ($request->has('new_user_name') && $request->has('new_user_email') && $request->has('new_user_password')) {
            $userToCreate = [
                'name' => $request->input('new_user_name'),
                'email' => $request->input('new_user_email'),
                'password' => $request->input('new_user_password'),
                'role' => 'PSYCHOLOGIST',
            ];
            Log::info('User creation data prepared', ['email' => $userToCreate['email']]);
        }
        
        // Start a database transaction so both user and profile are created together or not at all
        \DB::beginTransaction();
        
        try {
            // Create user first if needed
            if ($userToCreate) {
                $validated = validator($userToCreate, [
                    'name' => 'required',
                    'email' => 'required|email|unique:users',
                    'password' => 'required|min:6',
                    'role' => 'required|in:ADMIN,PSYCHOLOGIST,PATIENT',
                ])->validate();
                
                $user = \App\Models\User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => \Hash::make($validated['password']),
                    'role' => $validated['role'],
                ]);
                
                Log::info('User created successfully', ['user_id' => $user->id]);
                
                $data['user_id'] = $user->id;
            } elseif (empty($data['user_id'])) {
                $data['user_id'] = $request->user()->id;
            }
            
            Log::info('user_id set in data', ['user_id' => $data['user_id'] ?? 'NOT SET']);

            // Optional uploads (match update/storeSelf behavior)
            if ($request->hasFile('profile_image')) {
            try {
                $uploaded = Cloudinary::upload($request->file('profile_image')->getRealPath(), [
                    'folder' => 'psychologist_profiles',
                    'transformation' => ['width' => 800, 'height' => 800, 'crop' => 'limit'],
                ]);
                $url = method_exists($uploaded, 'getSecurePath') ? $uploaded->getSecurePath() : (method_exists($uploaded, 'getPath') ? $uploaded->getPath() : null);
                if ($url) {
                    $data['profile_image_url'] = $url;
                }
            } catch (\Throwable $e) {
                Log::warning('Cloudinary profile_image upload failed: '.$e->getMessage());
                $path = $request->file('profile_image')->store('psychologist_profiles', 'public');
                $data['profile_image_url'] = Storage::url($path);
            }
        }

        if ($request->hasFile('diploma_file')) {
            $file = $request->file('diploma_file');

            try {
                $uploaded = Cloudinary::uploadFile(
                    $file->getRealPath(),
                    [
                        'folder' => 'psychologist_profiles/diplomas',
                        'resource_type' => 'raw',
                        'use_filename' => true,
                        'unique_filename' => false,
                        'overwrite' => true,
                        'public_id' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                    ]
                );
                $url = $uploaded->getSecurePath();
                $data['diploma'] = str_replace('/image/upload/', '/raw/upload/', $url);
                Log::info('Diploma uploaded to Cloudinary', ['url' => $data['diploma']]);
            } catch (\Throwable $e) {
                Log::warning('Cloudinary diploma_file upload failed: '.$e->getMessage());
                $path = $file->store('psychologist_profiles/diplomas', 'public');
                $data['diploma'] = Storage::url($path);
                Log::info('Diploma stored locally', ['path' => $data['diploma']]);
            }
        } else {
            Log::error('No diploma_file in request!');
        }

        if ($request->hasFile('cin_file')) {
            $file = $request->file('cin_file');

            try {
                $uploaded = Cloudinary::uploadFile(
                    $file->getRealPath(),
                    [
                        'folder' => 'psychologist_profiles/cins',
                        'resource_type' => 'raw',
                        'use_filename' => true,
                        'unique_filename' => false,
                        'overwrite' => true,
                        'public_id' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                    ]
                );
                $url = $uploaded->getSecurePath();
                $data['cin'] = str_replace('/image/upload/', '/raw/upload/', $url);
                Log::info('CIN uploaded to Cloudinary', ['url' => $data['cin']]);
            } catch (\Throwable $e) {
                Log::warning('Cloudinary cin_file upload failed: '.$e->getMessage());
                $path = $file->store('psychologist_profiles/cins', 'public');
                $data['cin'] = Storage::url($path);
                Log::info('CIN stored locally', ['path' => $data['cin']]);
            }
        } else {
            Log::error('No cin_file in request!');
        }

        if ($request->hasFile('cv_file')) {
            $file = $request->file('cv_file');

            try {
                $uploaded = Cloudinary::uploadFile(
                    $file->getRealPath(),
                    [
                        'folder' => 'psychologist_profiles/cvs',
                        'resource_type' => 'raw',
                        'use_filename' => true,
                        'unique_filename' => false,
                        'overwrite' => true,
                        'public_id' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                    ]
                );
                $url = $uploaded->getSecurePath();
                $data['cv'] = str_replace('/image/upload/', '/raw/upload/', $url);
                Log::info('CV uploaded to Cloudinary', ['url' => $data['cv']]);
            } catch (\Throwable $e) {
                Log::warning('Cloudinary cv_file upload failed: '.$e->getMessage());
                $path = $file->store('psychologist_profiles/cvs', 'public');
                $data['cv'] = Storage::url($path);
                Log::info('CV stored locally', ['path' => $data['cv']]);
            }
        } else {
            Log::error('No cv_file in request!');
        }

        unset($data['profile_image'], $data['diploma_file'], $data['cin_file'], $data['cv_file']);

        Log::info('About to create profile', [
            'user_id' => $data['user_id'] ?? 'MISSING',
            'has_diploma' => isset($data['diploma']),
            'has_cin' => isset($data['cin']),
            'diploma_value' => $data['diploma'] ?? 'NOT SET',
            'cin_value' => $data['cin'] ?? 'NOT SET',
        ]);

        $profile = PsychologistProfile::create($data);

        $specialisationIds = array_values(array_unique(array_map('intval', $specialisationIds)));
        if (! empty($specialisationIds)) {
            $profile->specialisations()->sync($specialisationIds);
        }

        if (! empty($availabilities)) {
            // Each slot: day_of_week, start_time, end_time
            $profile->availabilities()->createMany($availabilities);
        }
        
        Log::info('Profile created successfully', ['profile_id' => $profile->id]);
        
        // Commit the transaction - both user and profile created successfully
        \DB::commit();

        // If this was an XHR/JSON request (our admin modal uses fetch), return JSON (no redirects).
        if ($request->expectsJson()) {
            return response()->json([
                'profile' => $profile->fresh()->load(['user', 'availabilities', 'specialisations']),
            ], 201);
        }

        // After creating via admin (non-XHR), go back to the list instead of opening Edit
        return redirect()->route('psychologist-profiles.index');
    } catch (ValidationException $e) {
        \DB::rollBack();

        if ($request->expectsJson()) {
            return response()->json(['errors' => $e->errors()], 422);
        }

        return back()->withErrors($e->errors())->withInput();
    } catch (QueryException $e) {
        \DB::rollBack();

        Log::error('Database error creating psychologist', [
            'error' => $e->getMessage(),
            'code' => $e->getCode(),
        ]);

        // Duplicate entry / unique constraint.
        if ($e->getCode() == 23000 || str_contains($e->getMessage(), 'Duplicate entry')) {
            $errors = [];

            // Most likely: duplicate email
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

        $fallbackErrors = ['general' => ['Database error: Unable to create psychologist']];
        if ($request->expectsJson()) {
            return response()->json(['errors' => $fallbackErrors], 500);
        }

        return back()->withErrors($fallbackErrors)->withInput();
    } catch (\Throwable $e) {
        // Rollback the transaction - nothing is created
        \DB::rollBack();
        
        Log::error('Error creating psychologist', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        $message = 'An error occurred while creating psychologist';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 500);
        }

        return back()->withErrors(['general' => $message])->withInput();
    }
}

    // Self-profile edit for logged-in psychologist
    public function editSelf(Request $request)
    {
        $user = $request->user();
        if (! $user || ! method_exists($user, 'isPsychologist') || ! $user->isPsychologist()) {
            abort(403);
        }

        $profile = $user->psychologistProfile;

        if (! $profile) {
            return Inertia::render('Psychologist/Profile/Create', [
                'user' => $user,
                'specialisations' => Specialisation::query()->orderBy('name')->get(['id', 'name']),
            ]);
        }

        return Inertia::render('Psychologist/Profile/Edit', [
            'profile' => $profile->load(['user', 'specialisations']),
            'specialisations' => Specialisation::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function storeSelf(PsychologistProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! method_exists($user, 'isPsychologist') || ! $user->isPsychologist()) {
            abort(403);
        }

        $data = $request->validated();
        $data['user_id'] = $user->id;

        $specialisationIds = [];
        if (array_key_exists('specialisation_ids', $data)) {
            $specialisationIds = is_array($data['specialisation_ids']) ? $data['specialisation_ids'] : [];
            unset($data['specialisation_ids']);
        }

        $usedCloudinary = false; // track if cloudinary was used for any file

        // files -> upload to Cloudinary, keep simple
        if ($request->hasFile('profile_image')) {
            try {
                $uploaded = Cloudinary::upload($request->file('profile_image')->getRealPath(), [
                    'folder' => 'psychologist_profiles',
                    'transformation' => ['width' => 800, 'height' => 800, 'crop' => 'limit'],
                ]);
                $url = method_exists($uploaded, 'getSecurePath') ? $uploaded->getSecurePath() : (method_exists($uploaded, 'getPath') ? $uploaded->getPath() : null);
                if (! $url) {
                    throw new \Exception('Cloudinary returned no URL for profile_image');
                }
                $data['profile_image_url'] = $url;
                $usedCloudinary = true;
                Log::info('Cloudinary profile_image upload succeeded', ['url' => $url, 'uploaded' => is_object($uploaded) ? (method_exists($uploaded, 'toArray') ? $uploaded->toArray() : json_decode(json_encode($uploaded), true)) : $uploaded]);
            } catch (\Throwable $e) {
                Log::warning('Cloudinary profile_image upload failed: '.$e->getMessage());
                $path = $request->file('profile_image')->store('psychologist_profiles', 'public');
                $data['profile_image_url'] = Storage::url($path);
            }
        }

        if ($request->hasFile('diploma_file')) {
            $file = $request->file('diploma_file');

            try {
                $uploaded = Cloudinary::uploadFile(
                    $file->getRealPath(),
                    [
                        'folder' => 'psychologist_profiles/diplomas',
                        'resource_type' => 'raw',
                        'use_filename' => true,
                        'unique_filename' => false,
                        'overwrite' => true,
                        'public_id' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                    ]
                );
                // Fix URL to use /raw/upload/ for proper PDF viewing
                $url = $uploaded->getSecurePath();
                $data['diploma'] = str_replace('/image/upload/', '/raw/upload/', $url);
                $usedCloudinary = true;
            } catch (\Throwable $e) {
                Log::warning('Cloudinary diploma_file upload failed: '.$e->getMessage());
                $path = $file->store('psychologist_profiles/diplomas', 'public');
                $data['diploma'] = Storage::url($path);
            }
        }

        if ($request->hasFile('cin_file')) {
            $file = $request->file('cin_file');

            try {
                $uploaded = Cloudinary::uploadFile(
                    $file->getRealPath(),
                    [
                        'folder' => 'psychologist_profiles/cins',
                        'resource_type' => 'raw',
                        'use_filename' => true,
                        'unique_filename' => false,
                        'overwrite' => true,
                        'public_id' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                    ]
                );
                // Fix URL to use /raw/upload/ for proper PDF viewing
                $url = $uploaded->getSecurePath();
                $data['cin'] = str_replace('/image/upload/', '/raw/upload/', $url);
                $usedCloudinary = true;
            } catch (\Throwable $e) {
                Log::warning('Cloudinary cin_file upload failed: '.$e->getMessage());
                $path = $file->store('psychologist_profiles/cins', 'public');
                $data['cin'] = Storage::url($path);
            }
        }

        if ($request->hasFile('cv_file')) {
            $file = $request->file('cv_file');

            try {
                $uploaded = Cloudinary::uploadFile(
                    $file->getRealPath(),
                    [
                        'folder' => 'psychologist_profiles/cvs',
                        'resource_type' => 'raw',
                        'use_filename' => true,
                        'unique_filename' => false,
                        'overwrite' => true,
                        'public_id' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                    ]
                );
                $url = $uploaded->getSecurePath();
                $data['cv'] = str_replace('/image/upload/', '/raw/upload/', $url);
                $usedCloudinary = true;
            } catch (\Throwable $e) {
                Log::warning('Cloudinary cv_file upload failed: '.$e->getMessage());
                $path = $file->store('psychologist_profiles/cvs', 'public');
                $data['cv'] = Storage::url($path);
            }
        }

        $profile = PsychologistProfile::create($data);

        $specialisationIds = array_values(array_unique(array_map('intval', $specialisationIds)));
        if (! empty($specialisationIds)) {
            $profile->specialisations()->sync($specialisationIds);
        }

        Log::info('Psychologist profile created', ['id' => $profile->id, 'usedCloudinary' => $usedCloudinary]);

        // If request is from Inertia, avoid navigation and return 204
        if ($request->header('X-Inertia')) {
            return response()->noContent();
        }

        return redirect()->route('psychologist.profile.edit');
    }

    public function updateSelf(PsychologistProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! method_exists($user, 'isPsychologist') || ! $user->isPsychologist()) {
            abort(403);
        }

        $profile = $user->psychologistProfile;
        if (! $profile) {
            abort(404);
        }

        // Get all validated input and use it directly for update
        $data = $request->validated();
        $usedCloudinary = false;

        $specialisationIds = null;
        if (array_key_exists('specialisation_ids', $data)) {
            $specialisationIds = is_array($data['specialisation_ids']) ? $data['specialisation_ids'] : [];
            unset($data['specialisation_ids']);
        }

        // DEBUG: Log what we received
        Log::info('updateSelf received data', [
            'validated' => $data,
            'all_input' => $request->all(),
            'profile_before' => $profile->toArray(),
        ]);

        if ($request->hasFile('profile_image')) {
            try {
                $uploaded = Cloudinary::upload($request->file('profile_image')->getRealPath(), [
                    'folder' => 'psychologist_profiles',
                    'transformation' => ['width' => 800, 'height' => 800, 'crop' => 'limit'],
                ]);
                $url = method_exists($uploaded, 'getSecurePath') ? $uploaded->getSecurePath() : (method_exists($uploaded, 'getPath') ? $uploaded->getPath() : null);
                if (! $url) {
                    throw new \Exception('Cloudinary returned no URL for profile_image');
                }
                $data['profile_image_url'] = $url;
                $usedCloudinary = true;
                Log::info('Cloudinary profile_image upload succeeded', ['url' => $url, 'uploaded' => is_object($uploaded) ? (method_exists($uploaded, 'toArray') ? $uploaded->toArray() : json_decode(json_encode($uploaded), true)) : $uploaded]);
            } catch (\Throwable $e) {
                Log::warning('Cloudinary profile_image upload failed: '.$e->getMessage());
                $path = $request->file('profile_image')->store('psychologist_profiles', 'public');
                $data['profile_image_url'] = Storage::url($path);
            }
        }

       if ($request->hasFile('diploma_file')) {
            $file = $request->file('diploma_file');
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension() ?: 'pdf';

            try {
                $uploaded = Cloudinary::uploadFile(
                    $file->getRealPath(),
                    [
                        'folder' => 'psychologist_profiles/diplomas',
                        'resource_type' => 'raw',
                        'use_filename' => true,
                        'unique_filename' => false,
                        'overwrite' => true,
                        'filename' => $originalName,
                        'format' => $extension,
                    ]
                );
                // Fix URL to use /raw/upload/ for proper PDF viewing
                $url = $uploaded->getSecurePath();
                $data['diploma'] = str_replace('/image/upload/', '/raw/upload/', $url);
                $usedCloudinary = true;
            } catch (\Throwable $e) {
                Log::warning('Cloudinary diploma_file upload failed: '.$e->getMessage());
                $path = $file->store('psychologist_profiles/diplomas', 'public');
                $data['diploma'] = Storage::url($path);
            }
        }

        if ($request->hasFile('cin_file')) {
            $file = $request->file('cin_file');
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension() ?: 'pdf';

            try {
                $uploaded = Cloudinary::uploadFile(
                    $file->getRealPath(),
                    [
                        'folder' => 'psychologist_profiles/cins',
                        'resource_type' => 'raw',
                        'use_filename' => true,
                        'unique_filename' => false,
                        'overwrite' => true,
                        'filename' => $originalName,
                        'format' => $extension,
                    ]
                );
                // Fix URL to use /raw/upload/ for proper PDF viewing
                $url = $uploaded->getSecurePath();
                $data['cin'] = str_replace('/image/upload/', '/raw/upload/', $url);
                $usedCloudinary = true;
            } catch (\Throwable $e) {
                Log::warning('Cloudinary cin_file upload failed: '.$e->getMessage());
                $path = $file->store('psychologist_profiles/cins', 'public');
                $data['cin'] = Storage::url($path);
            }
        }

        if ($request->hasFile('cv_file')) {
            $file = $request->file('cv_file');
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension() ?: 'pdf';

            try {
                $uploaded = Cloudinary::uploadFile(
                    $file->getRealPath(),
                    [
                        'folder' => 'psychologist_profiles/cvs',
                        'resource_type' => 'raw',
                        'use_filename' => true,
                        'unique_filename' => false,
                        'overwrite' => true,
                        'filename' => $originalName,
                        'format' => $extension,
                    ]
                );
                $url = $uploaded->getSecurePath();
                $data['cv'] = str_replace('/image/upload/', '/raw/upload/', $url);
                $usedCloudinary = true;
            } catch (\Throwable $e) {
                Log::warning('Cloudinary cv_file upload failed: '.$e->getMessage());
                $path = $file->store('psychologist_profiles/cvs', 'public');
                $data['cv'] = Storage::url($path);
            }
        }

        // Remove file inputs (not stored directly) and keep only fillable fields
        unset($data['profile_image'], $data['diploma_file'], $data['cin_file'], $data['cv_file']);

        // DEBUG: Log final data before save
        Log::info('updateSelf saving data', ['data_to_save' => $data]);

        // Update and save the profile
        $profile->fill($data);
        $saved = $profile->save();

        if ($specialisationIds !== null) {
            $specialisationIds = array_values(array_unique(array_map('intval', $specialisationIds)));
            $profile->specialisations()->sync($specialisationIds);
        }

        // DEBUG: Log result
        Log::info('Psychologist profile updated', [
            'id' => $profile->id,
            'saved' => $saved,
            'profile_after' => $profile->fresh()->toArray(),
            'usedCloudinary' => $usedCloudinary,
        ]);

        // If request is from Inertia, avoid navigation and return 204
        if ($request->header('X-Inertia')) {
            return response()->noContent();
        }

        return redirect()->route('psychologist.profile.edit');
    }

    public function update(PsychologistProfileRequest $request, PsychologistProfile $psychologistProfile)
    {
        // DEBUG: Log all incoming request data
        Log::info('Update request received', [
            'all_input' => $request->all(),
            'phone_raw' => $request->input('phone'),
            'country_code_raw' => $request->input('country_code'),
        ]);
        
        // Start with validated scalar fields
        $data = $request->validated();

        $specialisationIds = null;
        if (array_key_exists('specialisation_ids', $data)) {
            $specialisationIds = is_array($data['specialisation_ids']) ? $data['specialisation_ids'] : [];
            unset($data['specialisation_ids']);
        }

        // Pull out availability slots (if present) and update them separately.
        // PsychologistProfile is not fillable for this key.
        $availabilities = null;
        if (array_key_exists('availabilities', $data)) {
            $availabilities = $data['availabilities'];
            unset($data['availabilities']);
        }
        
        // DEBUG: Log validated data
        Log::info('Validated data', [
            'phone' => $data['phone'] ?? 'MISSING',
            'country_code' => $data['country_code'] ?? 'MISSING'
        ]);

        $usedCloudinary = false;

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            try {
                $uploaded = Cloudinary::upload($request->file('profile_image')->getRealPath(), [
                    'folder' => 'psychologist_profiles',
                    'transformation' => ['width' => 800, 'height' => 800, 'crop' => 'limit'],
                ]);
                $url = method_exists($uploaded, 'getSecurePath') ? $uploaded->getSecurePath() : (method_exists($uploaded, 'getPath') ? $uploaded->getPath() : null);
                if (! $url) {
                    throw new \Exception('Cloudinary returned no URL for profile_image');
                }
                $data['profile_image_url'] = $url;
                $usedCloudinary = true;
            } catch (\Throwable $e) {
                Log::warning('Cloudinary profile_image upload failed: '.$e->getMessage());
                $path = $request->file('profile_image')->store('psychologist_profiles', 'public');
                $data['profile_image_url'] = Storage::url($path);
            }
        }

        // Handle diploma PDF upload (expects 'diploma_file')
        if ($request->hasFile('diploma_file')) {
            $file = $request->file('diploma_file');
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension() ?: 'pdf';

            try {
                $uploaded = Cloudinary::uploadFile(
                    $file->getRealPath(),
                    [
                        'folder' => 'psychologist_profiles/diplomas',
                        'resource_type' => 'raw',
                        'use_filename' => true,
                        'unique_filename' => false,
                        'overwrite' => true,
                        'filename' => $originalName,
                        'format' => $extension,
                    ]
                );
                $url = $uploaded->getSecurePath();
                // Ensure raw upload path for proper viewing
                $data['diploma'] = str_replace('/image/upload/', '/raw/upload/', $url);
                $usedCloudinary = true;
            } catch (\Throwable $e) {
                Log::warning('Cloudinary diploma_file upload failed: '.$e->getMessage());
                $path = $file->store('psychologist_profiles/diplomas', 'public');
                $data['diploma'] = Storage::url($path);
            }
        }

        // Handle CIN PDF upload (expects 'cin_file')
        if ($request->hasFile('cin_file')) {
            $file = $request->file('cin_file');
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension() ?: 'pdf';

            try {
                $uploaded = Cloudinary::uploadFile(
                    $file->getRealPath(),
                    [
                        'folder' => 'psychologist_profiles/cins',
                        'resource_type' => 'raw',
                        'use_filename' => true,
                        'unique_filename' => false,
                        'overwrite' => true,
                        'filename' => $originalName,
                        'format' => $extension,
                    ]
                );
                $url = $uploaded->getSecurePath();
                $data['cin'] = str_replace('/image/upload/', '/raw/upload/', $url);
                $usedCloudinary = true;
            } catch (\Throwable $e) {
                Log::warning('Cloudinary cin_file upload failed: '.$e->getMessage());
                $path = $file->store('psychologist_profiles/cins', 'public');
                $data['cin'] = Storage::url($path);
            }
        }

        // Handle CV PDF upload (expects 'cv_file')
        if ($request->hasFile('cv_file')) {
            $file = $request->file('cv_file');
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension() ?: 'pdf';

            try {
                $uploaded = Cloudinary::uploadFile(
                    $file->getRealPath(),
                    [
                        'folder' => 'psychologist_profiles/cvs',
                        'resource_type' => 'raw',
                        'use_filename' => true,
                        'unique_filename' => false,
                        'overwrite' => true,
                        'filename' => $originalName,
                        'format' => $extension,
                    ]
                );
                $url = $uploaded->getSecurePath();
                $data['cv'] = str_replace('/image/upload/', '/raw/upload/', $url);
                $usedCloudinary = true;
            } catch (\Throwable $e) {
                Log::warning('Cloudinary cv_file upload failed: '.$e->getMessage());
                $path = $file->store('psychologist_profiles/cvs', 'public');
                $data['cv'] = Storage::url($path);
            }
        }

        // Remove raw file keys
        unset($data['profile_image'], $data['diploma_file'], $data['cin_file'], $data['cv_file']);

        Log::info('Admin update profile', ['id' => $psychologistProfile->id, 'usedCloudinary' => $usedCloudinary, 'data' => $data]);

        \DB::beginTransaction();
        try {
            $psychologistProfile->update($data);

            if ($specialisationIds !== null && is_array($specialisationIds)) {
                $specialisationIds = array_values(array_unique(array_map('intval', $specialisationIds)));
                $psychologistProfile->specialisations()->sync($specialisationIds);
            }

            // If the client sent availabilities (including empty array), replace existing.
            if ($availabilities !== null && is_array($availabilities)) {
                $psychologistProfile->availabilities()->delete();
                if (! empty($availabilities)) {
                    $psychologistProfile->availabilities()->createMany($availabilities);
                }
            }

            \DB::commit();
        } catch (\Throwable $e) {
            \DB::rollBack();
            throw $e;
        }

        if ($request->expectsJson()) {
            return response()->json([
                'profile' => $psychologistProfile->fresh()->load(['user', 'availabilities', 'specialisations']),
            ]);
        }

        // For admin modal edits (Inertia XHR), return 204 to avoid navigating to Edit page
        if ($request->header('X-Inertia')) {
            return response()->noContent();
        }

        // Fallback: return to index when not an Inertia request
        return redirect()->route('psychologist-profiles.index');
    }

    public function show(PsychologistProfile $psychologistProfile): Response
    {
        return Inertia::render('Admin/Psychologist/Show', [
            'profile' => $psychologistProfile->load(['user', 'availabilities', 'specialisations']),
        ]);
    }

    public function edit(PsychologistProfile $psychologistProfile): Response
    {
        return Inertia::render('Admin/Psychologist/Edit', [
            'profile' => $psychologistProfile->load(['user', 'availabilities', 'specialisations']),
            'specialisations' => Specialisation::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function destroy(PsychologistProfile $psychologistProfile): RedirectResponse
    {
        $psychologistProfile->delete();

        if (request()->expectsJson()) {
            return response()->noContent();
        }

        return redirect()->route('psychologist-profiles.index');
    }
}
