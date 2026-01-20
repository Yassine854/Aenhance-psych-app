<?php

namespace App\Http\Controllers\Psychologist;

use App\Http\Controllers\Controller;
use App\Models\PsychologistProfile;
use App\Models\Specialisation;
use App\Models\Expertise;
use App\Models\PsychologistVerificationDetails;
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
            'verification_details' => $profile ? $profile->verificationDetails : null,
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

    public function createVerification(Request $request): Response
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
        if (!$profile || !$profile->is_approved) {
            return redirect()->route('psychologist.profile.self')->with('error', 'Your profile must be approved first.');
        }

        $verification_details = $profile->verificationDetails;

        // Transform data for Vue component
        if ($verification_details) {
            $verification_details = $verification_details->load('diplomas')->toArray();
            // Convert single identity file URL to array for Vue component compatibility
            if (isset($verification_details['identity_file_url']) && $verification_details['identity_file_url']) {
                $verification_details['identity_files_urls'] = [$verification_details['identity_file_url']];
            } else {
                $verification_details['identity_files_urls'] = [];
            }
            // Convert diploma files to array for Vue component compatibility
            if (isset($verification_details['diplomas']) && is_array($verification_details['diplomas'])) {
                $verification_details['diploma_files_urls'] = array_map(function($diploma) {
                    return $diploma['file_url'];
                }, $verification_details['diplomas']);
            } else {
                $verification_details['diploma_files_urls'] = [];
            }
        }

        return Inertia::render('Psychologist/VerificationDetails', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'authUser' => $user,
            'verification_details' => $verification_details,
            'status' => session('status'),
        ]);
    }

    public function storeVerification(Request $request)
    {
        $user = $request->user();
        $profile = $user->psychologistProfile;

        if (!$profile || !$profile->is_approved) {
            return redirect()->route('psychologist.profile.self')->with('error', 'Your profile must be approved first.');
        }

        // If there is an existing verification record, make validation less strict (files optional) so
        // the psychologist can edit rejected verification details. If no existing record, require files.
        $existing = $profile->verificationDetails;

        $rules = [
            'rib' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:255',
            'bank_account_name' => 'nullable|string|max:255',
            'rib_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB
            'identity_type' => 'nullable|string|max:255',
            'identity_number' => 'nullable|string|max:255',
            'identity_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB
            'diploma_files' => 'nullable|array|min:1',
            'diploma_files.*' => 'file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB
        ];

        if (! $existing) {
            // first submission must provide required fields and at least one diploma
            $rules = [
                'rib' => 'required|string|max:255',
                'bank_name' => 'required|string|max:255',
                'bank_account_number' => 'required|string|max:255',
                'bank_account_name' => 'required|string|max:255',
                'rib_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB
                'identity_type' => 'required|string|max:255',
                'identity_number' => 'required|string|max:255',
                'identity_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB
                'diploma_files' => 'required|array|min:1',
                'diploma_files.*' => 'file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB
            ];
        }

        $validated = $request->validate($rules);

        // Upload or replace RIB file
        $ribUrl = $existing->rib_file_url ?? null;
        if ($request->hasFile('rib_file')) {
            // delete old if exists
            if (! empty($ribUrl)) {
                self::deleteCloudinaryFile($ribUrl);
            }
            try {
                $uploadedFile = Cloudinary::upload($request->file('rib_file')->getRealPath(), [
                    'folder' => 'psychologist_verifications/rib',
                    'public_id' => 'rib_' . $profile->id . '_' . time(),
                ]);
                $ribUrl = method_exists($uploadedFile, 'getSecurePath') ? $uploadedFile->getSecurePath() : null;
            } catch (\Throwable $e) {
                Log::warning('Cloudinary RIB upload failed: ' . $e->getMessage());
                return back()->withErrors(['rib_file' => 'Failed to upload RIB file. Please try again.']);
            }
        }

        // Upload or replace identity file
        $identityUrl = $existing->identity_file_url ?? null;
        if ($request->hasFile('identity_file')) {
            if (! empty($identityUrl)) {
                self::deleteCloudinaryFile($identityUrl);
            }
            try {
                $uploadedFile = Cloudinary::upload($request->file('identity_file')->getRealPath(), [
                    'folder' => 'psychologist_verifications/identity',
                    'public_id' => 'identity_' . $profile->id . '_' . time(),
                ]);
                $identityUrl = method_exists($uploadedFile, 'getSecurePath') ? $uploadedFile->getSecurePath() : null;
            } catch (\Throwable $e) {
                Log::warning('Cloudinary identity upload failed: ' . $e->getMessage());
                return back()->withErrors(['identity_file' => 'Failed to upload identity file. Please try again.']);
            }
        }

        // Create or update verification details
        if (! $existing) {
            $verificationDetails = PsychologistVerificationDetails::create([
                'psychologist_profile_id' => $profile->id,
                'rib' => $request->rib,
                'bank_name' => $request->bank_name,
                'bank_account_number' => $request->bank_account_number,
                'bank_account_name' => $request->bank_account_name,
                'rib_file_url' => $ribUrl,
                'identity_type' => $request->identity_type,
                'identity_number' => $request->identity_number,
                'identity_file_url' => $identityUrl,
                'verification_status' => 'pending',
            ]);
        } else {
            $existing->rib = $request->filled('rib') ? $request->rib : $existing->rib;
            $existing->bank_name = $request->filled('bank_name') ? $request->bank_name : $existing->bank_name;
            $existing->bank_account_number = $request->filled('bank_account_number') ? $request->bank_account_number : $existing->bank_account_number;
            $existing->bank_account_name = $request->filled('bank_account_name') ? $request->bank_account_name : $existing->bank_account_name;
            $existing->rib_file_url = $ribUrl;
            $existing->identity_type = $request->filled('identity_type') ? $request->identity_type : $existing->identity_type;
            $existing->identity_number = $request->filled('identity_number') ? $request->identity_number : $existing->identity_number;
            $existing->identity_file_url = $identityUrl;
            // When resubmitting, reset status to pending and clear rejection reason
            $existing->verification_status = 'pending';
            $existing->rejection_reason = null;
            $existing->save();
            $verificationDetails = $existing;
        }

        // If new diploma files uploaded, remove old diplomas and create new ones
        if ($request->hasFile('diploma_files')) {
            // delete old diploma files
            $oldDiplomas = $verificationDetails->diplomas()->get();
            foreach ($oldDiplomas as $d) {
                if (! empty($d->file_url)) {
                    self::deleteCloudinaryFile($d->file_url);
                }
            }
            $verificationDetails->diplomas()->delete();

            foreach ($request->file('diploma_files') as $file) {
                try {
                    $uploadedFile = Cloudinary::upload($file->getRealPath(), [
                        'folder' => 'psychologist_verifications/diplomas',
                        'public_id' => 'diploma_' . $verificationDetails->id . '_' . time() . '_' . uniqid(),
                    ]);
                    $url = method_exists($uploadedFile, 'getSecurePath') ? $uploadedFile->getSecurePath() : null;
                    if ($url) {
                        $verificationDetails->diplomas()->create(['file_url' => $url]);
                    }
                } catch (\Throwable $e) {
                    Log::warning('Cloudinary diploma upload failed: ' . $e->getMessage());
                }
            }
        }

        return redirect()->route('psychologist.verification.create')->with('status', 'Verification details submitted successfully. Your documents will be reviewed shortly.');
    }
}