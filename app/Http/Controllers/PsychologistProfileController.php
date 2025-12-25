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
        $profiles = PsychologistProfile::with('user')->paginate(15);

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

        $profile = PsychologistProfile::create($data);

        return redirect()->route('psychologist-profiles.edit', $profile);
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
            $file->getRealPath(), // path to temp file
            [
                'folder' => 'psychologist_profiles/diplomas', // different folder
                'resource_type' => 'raw',
                'use_filename' => true,
                'unique_filename' => false,
                'overwrite' => true,
                'public_id' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), // force name
            ]
        );

        $data['diploma'] = $uploaded->getSecurePath(); // store URL in diploma field
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
            $file->getRealPath(), // path to temp file
            [
                'folder' => 'psychologist_profiles/cins',
                'resource_type' => 'raw',
                'use_filename' => true,
                'unique_filename' => false,
                'overwrite' => true,
                'public_id' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), // force name
            ]
        );

        $data['cin'] = $uploaded->getSecurePath();
        $usedCloudinary = true;
    } catch (\Throwable $e) {
        Log::warning('Cloudinary cin_file upload failed: '.$e->getMessage());
        $path = $file->store('psychologist_profiles/cins', 'public');
        $data['cin'] = Storage::url($path);
    }
}

        $profile = PsychologistProfile::create($data);

        Log::info('Psychologist profile created', ['id' => $profile->id, 'usedCloudinary' => $usedCloudinary]);

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

        $data = $request->validated();

        $usedCloudinary = false; // track if cloudinary was used for any file

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
                'format' => $extension, // force correct extension
            ]
        );
        $data['diploma'] = $uploaded->getSecurePath();
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
                'format' => $extension, // force correct extension
            ]
        );
        $data['cin'] = $uploaded->getSecurePath();
        $usedCloudinary = true;
    } catch (\Throwable $e) {
        Log::warning('Cloudinary cin_file upload failed: '.$e->getMessage());
        $path = $file->store('psychologist_profiles/cins', 'public');
        $data['cin'] = Storage::url($path);
    }
}

        

        $profile->update($data);

        Log::info('Psychologist profile updated', ['id' => $profile->id, 'usedCloudinary' => $usedCloudinary]);

        return redirect()->route('psychologist.profile.edit');
    }

    public function update(PsychologistProfileRequest $request, PsychologistProfile $psychologistProfile): RedirectResponse
    {
        $psychologistProfile->update($request->validated());

        return redirect()->route('psychologist-profiles.edit', $psychologistProfile);
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
