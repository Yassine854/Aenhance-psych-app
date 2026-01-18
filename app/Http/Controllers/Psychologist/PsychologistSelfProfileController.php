<?php

namespace App\Http\Controllers\Psychologist;

use App\Http\Controllers\Controller;
use App\Models\PsychologistProfile;
use App\Models\Specialisation;
use App\Models\Expertise;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PsychologistSelfProfileController extends Controller
{
    /**
     * Extract Cloudinary public_id from a Cloudinary URL.
     * Example: https://res.cloudinary.com/dtaszolbl/image/upload/v1768434301/psychologist_profiles/cvs/gen%20ai.pdf
     * Returns: psychologist_profiles/cvs/gen ai
     */
    public static function getCloudinaryPublicId($url)
    {
        $parts = parse_url($url);
            if (empty($url)) return null;

            $path = parse_url($url, PHP_URL_PATH);
            if (! $path) return null;

            // Find the '/upload/' anchor which comes after the cloud name segment.
            $anchor = '/upload/';
            $pos = strpos($path, $anchor);
            if ($pos !== false) {
                $p = substr($path, $pos + strlen($anchor));
            } else {
                return null;
            }

            // Remove optional version prefix like 'v123456/'
            $p = preg_replace('#^v\d+/#', '', $p);
            // Remove file extension
            $p = preg_replace('/\.[^.\/]+$/', '', $p);
            // Trim any leading/trailing slashes and decode
            return urldecode(trim($p, '/'));
    }

    /**
     * Delete a Cloudinary resource by its full URL.
     * Tries as image first, then raw. Returns true on success.
     */
    private static function deleteCloudinaryFile(?string $url): bool
    {
        if (empty($url)) return false;

        $publicId = self::getCloudinaryPublicId($url);
        if (empty($publicId)) return false;
        try {
            // Try Admin API delete as image first (PDFs in your account appear under image)
            try {
                $result = Cloudinary::admin()->deleteAssets([$publicId], ['resource_type' => 'image']);
                if (is_array($result) && isset($result['deleted']) && isset($result['deleted'][$publicId]) && in_array(strtolower((string)$result['deleted'][$publicId]), ['deleted', 'not_found'], true)) return true;
            } catch (\Throwable $e) {}

            // Try Admin API delete as raw
            try {
                $result = Cloudinary::admin()->deleteAssets([$publicId], ['resource_type' => 'raw']);
                if (is_array($result) && isset($result['deleted']) && isset($result['deleted'][$publicId]) && in_array(strtolower((string)$result['deleted'][$publicId]), ['deleted', 'not_found'], true)) return true;
            } catch (\Throwable $e) {}

            // Fallback to uploader destroy for raw then image
            try {
                $r = Cloudinary::uploadApi()->destroy($publicId, ['resource_type' => 'raw']);
                if (is_array($r) && isset($r['result']) && in_array(strtolower((string)$r['result']), ['ok', 'deleted', 'not found', 'not_found'], true)) return true;
            } catch (\Throwable $e) {}

            try {
                $r2 = Cloudinary::uploadApi()->destroy($publicId, ['resource_type' => 'image']);
                if (is_array($r2) && isset($r2['result']) && in_array(strtolower((string)$r2['result']), ['ok', 'deleted', 'not found', 'not_found'], true)) return true;
            } catch (\Throwable $e) {}

            return false;
        } catch (\Throwable $e) {
            Log::error('deleteCloudinaryFile unexpected error: '.$e->getMessage(), ['public_id' => $publicId]);
            return false;
        }
    }
    public function edit(Request $request): Response
    {
        $user = $request->user();
        if (! $user || ! method_exists($user, 'isPsychologist') || ! $user->isPsychologist()) {
            return Inertia::render('Welcome', [
                'canLogin' => Route::has('login'),
                'canRegister' => Route::has('register'),
                'authUser' => $user,
            ]);
        }

        $profile = $user->psychologistProfile;

        return Inertia::render('Psychologist/Profile', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'authUser' => $user,
            'profile' => $profile ? $profile->load(['diplomas', 'specialisations', 'expertises']) : null,
            'specialisations' => Specialisation::all(),
            'expertises' => Expertise::all(),
            'cv_required' => $profile ? !$profile->cv : true,
            'diplomas_required' => $profile ? $profile->diplomas->isEmpty() : true,
            'status' => session('status'),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $profile = $user->psychologistProfile;

        $rules = [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date'],
            'gender' => ['nullable', 'string', 'max:50'],
            'country' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['required', 'string', 'max:50'],
            'country_code' => ['nullable', 'string', 'max:10'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'price_per_session' => ['required', 'numeric', 'min:0'],
            'languages' => ['nullable', 'array'],
            'languages.*' => ['string', 'max:50'],
            'specialisation_ids' => ['required', 'array', 'min:1'],
            'specialisation_ids.*' => ['integer', 'exists:specialisations,id'],
            'expertise_ids' => ['required', 'array', 'min:1'],
            'expertise_ids.*' => ['integer', 'exists:expertises,id'],
            'remove_profile_image' => ['nullable', 'boolean'],
            'profile_image' => ['nullable', 'image', 'max:5120'],
            'cv' => ['nullable', 'file', 'max:10240'],
            'diploma_files' => ['nullable', 'array'],
            'diploma_files.*' => ['file', 'max:10240'],
            'remove_diplomas' => ['nullable', 'array'],
            'remove_diplomas.*' => ['integer'],
        ];

        if (!$profile->cv) {
            $rules['cv'] = ['required', 'file', 'max:10240'];
        }

        if ($profile->diplomas->isEmpty()) {
            $rules['diploma_files'] = ['required', 'array', 'min:1'];
            $rules['diploma_files.*'] = ['file', 'max:10240'];
        }

        $validated = $request->validate($rules);

        $data = $validated;

        // Handle profile image
        if (! empty($data['remove_profile_image'])) {
            $data['profile_image_url'] = null;
        }

        if ($request->hasFile('profile_image')) {
            // Delete old profile image if exists
            if (!empty($profile->profile_image_url)) {
                self::deleteCloudinaryFile($profile->profile_image_url);
            }
            try {
                $uploaded = Cloudinary::upload($request->file('profile_image')->getRealPath(), [
                    'folder' => 'psychologist_profiles',
                    'transformation' => ['width' => 800, 'height' => 800, 'crop' => 'limit'],
                ]);

                $url = method_exists($uploaded, 'getSecurePath')
                    ? $uploaded->getSecurePath()
                    : (method_exists($uploaded, 'getPath') ? $uploaded->getPath() : null);

                if ($url) {
                    $data['profile_image_url'] = $url;
                }
            } catch (\Throwable $e) {
                Log::warning('Cloudinary psychologist profile_image upload failed: '.$e->getMessage());
                $path = $request->file('profile_image')->store('psychologist_profiles', 'public');
                $data['profile_image_url'] = Storage::url($path);
            }
        }

        // Handle CV
        if ($request->hasFile('cv')) {
            // Delete old CV if exists
            if (!empty($profile->cv)) {
                self::deleteCloudinaryFile($profile->cv);
            }
            try {
                $uploaded = Cloudinary::uploadFile($request->file('cv')->getRealPath(), [
                    'folder' => 'psychologist_profiles/cvs',
                    'resource_type' => 'raw',
                ]);

                $url = method_exists($uploaded, 'getSecurePath')
                    ? $uploaded->getSecurePath()
                    : (method_exists($uploaded, 'getPath') ? $uploaded->getPath() : null);

                if ($url) {
                    $data['cv'] = $url;
                }
            } catch (\Throwable $e) {
                Log::warning('Cloudinary psychologist CV upload failed: '.$e->getMessage());
                $path = $request->file('cv')->store('psychologist_cvs', 'public');
                $data['cv'] = Storage::url($path);
            }
        }

        // Clean up data
        unset($data['profile_image'], $data['cv']);
        unset($data['diploma_files'], $data['remove_diplomas']);

        // Update or create profile
        $profile = PsychologistProfile::updateOrCreate(
            ['user_id' => $user->id],
            array_merge($data, ['user_id' => $user->id])
        );

        // Handle specialisations and expertises
        if (isset($validated['specialisation_ids'])) {
            $profile->specialisations()->sync($validated['specialisation_ids']);
        }

        if (isset($validated['expertise_ids'])) {
            $profile->expertises()->sync($validated['expertise_ids']);
        }

        // Handle diploma uploads and removals
        if (!empty($validated['remove_diplomas'])) {
            $diplomasToRemove = $profile->diplomas()->whereIn('id', $validated['remove_diplomas'])->get();
            foreach ($diplomasToRemove as $diploma) {
                if (!empty($diploma->file_url)) {
                    self::deleteCloudinaryFile($diploma->file_url);
                }
            }
            $profile->diplomas()->whereIn('id', $validated['remove_diplomas'])->delete();
        }

        if ($request->hasFile('diploma_files')) {
            foreach ($request->file('diploma_files') as $file) {
                try {
                    $uploaded = Cloudinary::uploadFile($file->getRealPath(), [
                        'folder' => 'psychologist_profiles/diplomas',
                        'resource_type' => 'raw',
                    ]);

                    $url = method_exists($uploaded, 'getSecurePath')
                        ? $uploaded->getSecurePath()
                        : (method_exists($uploaded, 'getPath') ? $uploaded->getPath() : null);

                    if ($url) {
                        $profile->diplomas()->create(['file_url' => $url]);
                    }
                } catch (\Throwable $e) {
                    Log::warning('Cloudinary psychologist diploma upload failed: '.$e->getMessage());
                }
            }
        }

        return redirect()->route('psychologist.profile.self')->with('status', 'Profile updated successfully.');
    }
}