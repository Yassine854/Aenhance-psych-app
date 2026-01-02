<?php

namespace App\Http\Controllers;

use App\Http\Requests\PsychologistProfileRequest;
use App\Models\PsychologistProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PsychologistProfileController extends Controller
{
    public function index(Request $request)
    {
        // Load psychologist profiles with their users efficiently
        $profiles = PsychologistProfile::with(['user' => function ($query) {
            $query->where('role', 'PSYCHOLOGIST');
        }])->whereHas('user', function ($query) {
            $query->where('role', 'PSYCHOLOGIST');
        })->paginate(15);

        if ($request->wantsJson()) {
            return response()->json($profiles);
        }

        return Inertia::render('Admin/Psychologist/Index', [
            'profiles' => $profiles,
        ]);
    }

    // Local-only helper to return JSON without auth (for Postman testing)
    public function testJson()
    {
        if (!app()->environment('local')) {
            abort(404);
        }

        return response()->json(PsychologistProfile::with('user')->get());
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Psychologist/Create');
    }

    public function store(PsychologistProfileRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if (empty($data['user_id'])) {
            $data['user_id'] = $request->user()->id;
        }

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
                $url = $uploaded->getSecurePath();
                $data['cin'] = str_replace('/image/upload/', '/raw/upload/', $url);
            } catch (\Throwable $e) {
                Log::warning('Cloudinary cin_file upload failed: '.$e->getMessage());
                $path = $file->store('psychologist_profiles/cins', 'public');
                $data['cin'] = Storage::url($path);
            }
        }

        unset($data['profile_image'], $data['diploma_file'], $data['cin_file']);

        $profile = PsychologistProfile::create($data);

        // After creating via admin, go back to the list instead of opening Edit
        return redirect()->route('psychologist-profiles.index');
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
            ]);
        }

        return Inertia::render('Psychologist/Profile/Edit', [
            'profile' => $profile->load('user'),
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

        $profile = PsychologistProfile::create($data);

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

        // Remove file inputs (not stored directly) and keep only fillable fields
        unset($data['profile_image'], $data['diploma_file'], $data['cin_file']);

        // DEBUG: Log final data before save
        Log::info('updateSelf saving data', ['data_to_save' => $data]);

        // Update and save the profile
        $profile->fill($data);
        $saved = $profile->save();

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

        // Remove raw file keys
        unset($data['profile_image'], $data['diploma_file'], $data['cin_file']);

        Log::info('Admin update profile', ['id' => $psychologistProfile->id, 'usedCloudinary' => $usedCloudinary, 'data' => $data]);

        $psychologistProfile->update($data);

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
            'profile' => $psychologistProfile->load('user'),
        ]);
    }

    public function edit(PsychologistProfile $psychologistProfile): Response
    {
        return Inertia::render('Admin/Psychologist/Edit', [
            'profile' => $psychologistProfile->load('user'),
        ]);
    }

    public function destroy(PsychologistProfile $psychologistProfile): RedirectResponse
    {
        $psychologistProfile->delete();

        return redirect()->route('psychologist-profiles.index');
    }
}
