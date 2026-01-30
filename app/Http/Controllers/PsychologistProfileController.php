<?php

namespace App\Http\Controllers;

use App\Http\Requests\PsychologistProfileRequest;
use App\Models\PsychologistProfile;
use App\Models\PsychologistDiploma;
use App\Models\PsychologistVerificationDetails;
use App\Models\Specialisation;
use App\Models\Expertise;
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
use App\Services\ActivityLogger;

class PsychologistProfileController extends Controller
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
                // Fallback: remove first segment (likely cloud name) then proceed
                $trimmed = ltrim($path, '/');
                $parts = explode('/', $trimmed);
                if (count($parts) > 1 && ($parts[1] === 'image' || $parts[1] === 'raw' || $parts[0] !== 'image')) {
                    // remove first segment (cloud name)
                    array_shift($parts);
                }
                $p = implode('/', $parts);
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
                $res = Cloudinary::admin()->deleteAssets([$publicId], ['resource_type' => 'image', 'invalidate' => true]);
                Log::info('Cloudinary admin deleteAssets(image) response', ['public_id' => $publicId, 'response' => $res]);
                if (is_object($res)) $arr = $res->getArrayCopy(); else $arr = (array)$res;
                if (isset($arr['deleted']) && array_key_exists($publicId, $arr['deleted']) && $arr['deleted'][$publicId] === 'deleted') {
                    return true;
                }
            } catch (\Throwable $e) {
                Log::warning('Admin deleteAssets(image) failed: '.$e->getMessage(), ['public_id' => $publicId]);
            }

            // Try Admin API delete as raw
            try {
                $res2 = Cloudinary::admin()->deleteAssets([$publicId], ['resource_type' => 'raw', 'invalidate' => true]);
                Log::info('Cloudinary admin deleteAssets(raw) response', ['public_id' => $publicId, 'response' => $res2]);
                if (is_object($res2)) $arr2 = $res2->getArrayCopy(); else $arr2 = (array)$res2;
                if (isset($arr2['deleted']) && array_key_exists($publicId, $arr2['deleted']) && $arr2['deleted'][$publicId] === 'deleted') {
                    return true;
                }
            } catch (\Throwable $e) {
                Log::warning('Admin deleteAssets(raw) failed: '.$e->getMessage(), ['public_id' => $publicId]);
            }

            // Fallback to uploader destroy for raw then image
            try {
                $r = Cloudinary::destroy($publicId, ['resource_type' => 'raw', 'invalidate' => true]);
                Log::info('Cloudinary destroy(raw) fallback response', ['public_id' => $publicId, 'response' => $r]);
                if (is_array($r) && isset($r['result']) && in_array(strtolower((string)$r['result']), ['ok', 'deleted', 'not found', 'not_found'], true)) return true;
            } catch (\Throwable $e) {
                Log::warning('Destroy(raw) fallback failed: '.$e->getMessage(), ['public_id' => $publicId]);
            }

            try {
                $r2 = Cloudinary::destroy($publicId, ['resource_type' => 'image', 'invalidate' => true]);
                Log::info('Cloudinary destroy(image) fallback response', ['public_id' => $publicId, 'response' => $r2]);
                if (is_array($r2) && isset($r2['result']) && in_array(strtolower((string)$r2['result']), ['ok', 'deleted', 'not found', 'not_found'], true)) return true;
            } catch (\Throwable $e) {
                Log::warning('Destroy(image) fallback failed: '.$e->getMessage(), ['public_id' => $publicId]);
            }

            return false;
        } catch (\Throwable $e) {
            Log::error('deleteCloudinaryFile unexpected error: '.$e->getMessage(), ['public_id' => $publicId]);
            return false;
        }
    }
    /**
     * Display a listing of psychologist profiles for admin.
     */
    public function index(Request $request): Response
    {
        $query = PsychologistProfile::query()->with(['user', 'specialisations', 'expertises', 'diplomas', 'availabilities']);

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->whereHas('user', function($u) use ($q) {
                $u->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%");
            })->orWhere('phone', 'like', "%{$q}%");
        }

        $profiles = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        return Inertia::render('Admin/Psychologist/Index', [
            'profiles' => $profiles,
            'filters' => $request->only(['q']),
            'specialisations' => Specialisation::query()->orderBy('name')->get(['id', 'name']),
            'expertises' => Expertise::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Psychologist/Create', [
            'specialisations' => Specialisation::query()->orderBy('name')->get(['id', 'name']),
            'expertises' => Expertise::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(PsychologistProfileRequest $request): HttpResponse
    {
        Log::info('Store method called', [
            'has_new_user_name' => $request->has('new_user_name'),
            'has_diploma_file' => $request->hasFile('diploma_file'),
        ]);
        
        $data = $request->validated();

        $specialisationIds = [];
        if (array_key_exists('specialisation_ids', $data)) {
            $specialisationIds = is_array($data['specialisation_ids']) ? $data['specialisation_ids'] : [];
            unset($data['specialisation_ids']);
        }

        $expertiseIds = [];
        if (array_key_exists('expertise_ids', $data)) {
            $expertiseIds = $this->resolveExpertiseItems($data['expertise_ids']);
            unset($data['expertise_ids']);
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
                Log::error('Cloudinary profile_image upload failed: '.$e->getMessage());
                throw new \Exception('Profile image upload failed: '.$e->getMessage());
            }
        }

        // Collect diploma uploads; allow multiple files under 'diploma_files[]' or single 'diploma_file' for backward compatibility
        $diplomaUploads = [];
        if ($request->hasFile('diploma_files')) {
            $files = $request->file('diploma_files');
            if (!is_array($files)) $files = [$files];
        } elseif ($request->hasFile('diploma_file')) {
            $files = [$request->file('diploma_file')];
        } else {
            $files = [];
        }

        foreach ($files as $file) {
            if (! $file) continue;
            try {
                $uploaded = Cloudinary::uploadFile(
                    $file->getRealPath(),
                    [
                        'folder' => 'psychologist_profiles/diplomas',
                        'resource_type' => 'raw',
                        'type' => 'upload',
                        'access_mode' => 'public',
                        'use_filename' => true,
                        'unique_filename' => false,
                        'overwrite' => true,
                        'public_id' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                    ]
                );
                $url = $uploaded->getSecurePath();
                // Always ensure /raw/upload/ for diplomas
                if (str_contains($url, '/image/upload/')) {
                    $url = str_replace('/image/upload/', '/raw/upload/', $url);
                } else if (!str_contains($url, '/raw/upload/')) {
                    // fallback: force raw path
                    $url = preg_replace('#/(?:v\d+/)?upload/#', '/raw/upload/', $url);
                }
                $diplomaUploads[] = ['file_url' => $url];
                Log::info('Diploma uploaded to Cloudinary', ['url' => $url]);
            } catch (\Throwable $e) {
                Log::error('Cloudinary diploma_file upload failed: '.$e->getMessage());
                throw new \Exception('Diploma upload failed: '.$e->getMessage());
            }
        }

        // CIN field removed - no longer processing cin_file

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
                // Always ensure /raw/upload/ for CVs
                if (str_contains($url, '/image/upload/')) {
                    $url = str_replace('/image/upload/', '/raw/upload/', $url);
                } else if (!str_contains($url, '/raw/upload/')) {
                    $url = preg_replace('#/(?:v\d+/)?upload/#', '/raw/upload/', $url);
                }
                $data['cv'] = $url;
                Log::info('CV uploaded to Cloudinary', ['url' => $url]);
            } catch (\Throwable $e) {
                Log::error('Cloudinary cv_file upload failed: '.$e->getMessage());
                throw new \Exception('CV upload failed: '.$e->getMessage());
            }
        }


        $profile = PsychologistProfile::create($data);

        // Attach any uploaded diplomas
        if (! empty($diplomaUploads)) {
            foreach ($diplomaUploads as $d) {
                $profile->diplomas()->create($d);
            }
        }

        $specialisationIds = array_values(array_unique(array_map('intval', $specialisationIds)));
        if (! empty($specialisationIds)) {
            $profile->specialisations()->sync($specialisationIds);
        }

        $expertiseIds = array_values(array_unique(array_map('intval', $expertiseIds)));
        if (! empty($expertiseIds)) {
            $profile->expertises()->sync($expertiseIds);
        }

        $expertiseIds = array_values(array_unique(array_map('intval', $expertiseIds)));
        if (! empty($expertiseIds)) {
            $profile->expertises()->sync($expertiseIds);
        }

        if (! empty($availabilities)) {
            // Each slot: day_of_week, start_time, end_time
            $profile->availabilities()->createMany($availabilities);
        }
        
        Log::info('Profile created successfully', ['profile_id' => $profile->id]);
        ActivityLogger::log($request->user()->id ?? null, 'ADMIN', 'created_psychologist_profile', 'PsychologistProfile', $profile->id, 'Psychologist profile created');
        
        // Commit the transaction - both user and profile created successfully
        \DB::commit();

        // If this was an XHR/JSON request (our admin modal uses fetch), return JSON (no redirects).
        if ($request->expectsJson()) {
            return response()->json([
                'profile' => $profile->fresh()->load(['user', 'availabilities', 'specialisations', 'expertises']),
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

        $expertiseIds = null;
        if (array_key_exists('expertise_ids', $data)) {
            $expertiseIds = $this->resolveExpertiseItems($data['expertise_ids']);
            unset($data['expertise_ids']);
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
            // Delete existing profile image if present
            if (! empty($psychologistProfile->profile_image_url)) {
                try {
                    self::deleteCloudinaryFile($psychologistProfile->profile_image_url);
                } catch (\Throwable $e) {
                    Log::error('Failed to delete previous profile_image (admin update): '.$e->getMessage());
                }
            }
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
                Log::error('Cloudinary profile_image upload failed: '.$e->getMessage());
                throw new \Exception('Profile image upload failed: '.$e->getMessage());
            }
        }

        // Handle diploma PDF upload (expects 'diploma_file')
        // Support multiple diploma uploads on admin update: attach after update
        $diplomaUploads = [];
        if ($request->hasFile('diploma_files')) {
            $files = $request->file('diploma_files');
            if (!is_array($files)) $files = [$files];
        } elseif ($request->hasFile('diploma_file')) {
            $files = [$request->file('diploma_file')];
        } else {
            $files = [];
        }

        if (!empty($files)) {
            // Delete existing diplomas
            $existingDiplomas = $psychologistProfile->diplomas;
            foreach ($existingDiplomas as $diploma) {
                self::deleteCloudinaryFile($diploma->file_url);
                $diploma->delete();
            }
        }

        foreach ($files as $file) {
            if (! $file) continue;
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension() ?: 'pdf';

            try {
                $uploaded = Cloudinary::upload(
                    $file->getRealPath(),
                    [
                        'folder' => 'psychologist_profiles/diplomas',
                        'resource_type' => 'image',
                        'use_filename' => true,
                        'unique_filename' => false,
                        'overwrite' => true,
                        'public_id' => $originalName,
                        'format' => $extension,
                    ]
                );
                $url = method_exists($uploaded, 'getSecurePath') ? $uploaded->getSecurePath() : (method_exists($uploaded, 'getPath') ? $uploaded->getPath() : null);
                $diplomaUploads[] = ['file_url' => $url];
                $usedCloudinary = true;
            } catch (\Throwable $e) {
                Log::error('Cloudinary diploma_file upload failed: '.$e->getMessage());
                throw new \Exception('Diploma upload failed: '.$e->getMessage());
            }
        }

        // If there are new diploma uploads, any existing diplomas not kept
        // will already have been deleted above; new diplomas will be attached below.

        // CIN field removed - no longer processing cin_file on admin update

        // Handle CV PDF upload (expects 'cv_file')
        if ($request->hasFile('cv_file')) {
            // Delete previous CV if present
            if (! empty($psychologistProfile->cv)) {
                try {
                    self::deleteCloudinaryFile($psychologistProfile->cv);
                } catch (\Throwable $e) {
                    Log::error('Failed to delete previous CV (admin update): '.$e->getMessage());
                }
            }
            $file = $request->file('cv_file');
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension() ?: 'pdf';

            try {
                $uploaded = Cloudinary::upload(
                    $file->getRealPath(),
                    [
                        'folder' => 'psychologist_profiles/cvs',
                        'resource_type' => 'image',
                        'use_filename' => true,
                        'unique_filename' => false,
                        'overwrite' => true,
                        'public_id' => $originalName,
                        'format' => $extension,
                    ]
                );
                $url = method_exists($uploaded, 'getSecurePath') ? $uploaded->getSecurePath() : (method_exists($uploaded, 'getPath') ? $uploaded->getPath() : null);
                $data['cv'] = $url;
                $usedCloudinary = true;
            } catch (\Throwable $e) {
                Log::error('Cloudinary cv_file upload failed: '.$e->getMessage());
                throw new \Exception('CV upload failed: '.$e->getMessage());
            }
        }

        // Remove raw file keys
        unset($data['profile_image'], $data['diploma_file'], $data['diploma_files'], $data['cv_file']);

        Log::info('Admin update profile', ['id' => $psychologistProfile->id, 'usedCloudinary' => $usedCloudinary, 'data' => $data]);

        \DB::beginTransaction();
        try {
            $psychologistProfile->update($data);

            if ($specialisationIds !== null && is_array($specialisationIds)) {
                $specialisationIds = array_values(array_unique(array_map('intval', $specialisationIds)));
                $psychologistProfile->specialisations()->sync($specialisationIds);
            }

            if ($expertiseIds !== null && is_array($expertiseIds)) {
                $expertiseIds = array_values(array_unique(array_map('intval', $expertiseIds)));
                $psychologistProfile->expertises()->sync($expertiseIds);
            }

            // If the client sent availabilities (including empty array), replace existing.
            if ($availabilities !== null && is_array($availabilities)) {
                $psychologistProfile->availabilities()->delete();
                if (! empty($availabilities)) {
                    $psychologistProfile->availabilities()->createMany($availabilities);
                }
            }

            // Attach any uploaded diplomas on update
            if (! empty($diplomaUploads)) {
                foreach ($diplomaUploads as $d) {
                    $psychologistProfile->diplomas()->create($d);
                }
            }

            \DB::commit();
        } catch (\Throwable $e) {
            \DB::rollBack();
            throw $e;
        }

        // Log the update for audit (run for all request types)
        ActivityLogger::log(
            $request->user()->id ?? null,
            $request->user()?->role ?? 'ADMIN',
            'updated_psychologist_profile',
            'PsychologistProfile',
            $psychologistProfile->id,
            'Psychologist profile updated'
        );

        if ($request->expectsJson()) {
            return response()->json([
                'profile' => $psychologistProfile->fresh()->load(['user', 'availabilities', 'specialisations', 'expertises']),
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
        $loaded = $psychologistProfile->load(['user', 'availabilities', 'specialisations', 'expertises', 'diplomas', 'verificationDetails.diplomas']);

        // Prepare verification details for frontend similar to other endpoints
        $verification = null;
        if ($loaded->verificationDetails) {
            $verification = $loaded->verificationDetails->load('diplomas')->toArray();
            if (isset($verification['identity_file_url']) && $verification['identity_file_url']) {
                $verification['identity_files_urls'] = [$verification['identity_file_url']];
            } else {
                $verification['identity_files_urls'] = [];
            }
            if (isset($verification['diplomas']) && is_array($verification['diplomas'])) {
                $verification['diploma_files_urls'] = array_map(function ($d) { return $d['file_url']; }, $verification['diplomas']);
            } else {
                $verification['diploma_files_urls'] = [];
            }
        }

        $psychologistArray = $loaded->toArray();
        $psychologistArray['verification_details'] = $verification;

        return Inertia::render('Admin/Psychologist/Show', [
            // keep existing 'profile' key for backwards compatibility
            'profile' => $loaded,
            // also provide 'psychologist' so the Vue modal component receives the prop it expects
            'psychologist' => $psychologistArray,
        ]);
    }

    public function edit(PsychologistProfile $psychologistProfile): Response
    {
        $loaded = $psychologistProfile->load(['user', 'availabilities', 'specialisations', 'expertises', 'diplomas', 'verificationDetails.diplomas']);

        // Prepare transformed verification details for the Vue component (snake_case keys expected)
        $verification = null;
        if ($loaded->verificationDetails) {
            $verification = $loaded->verificationDetails->load('diplomas')->toArray();
            // normalize identity file to an array for frontend
            if (isset($verification['identity_file_url']) && $verification['identity_file_url']) {
                $verification['identity_files_urls'] = [$verification['identity_file_url']];
            } else {
                $verification['identity_files_urls'] = [];
            }
            // collect diploma file urls
            if (isset($verification['diplomas']) && is_array($verification['diplomas'])) {
                $verification['diploma_files_urls'] = array_map(function ($d) { return $d['file_url']; }, $verification['diplomas']);
            } else {
                $verification['diploma_files_urls'] = [];
            }
        }

        // Convert loaded model to array and attach snake_case `verification_details` key
        $psychologistArray = $loaded->toArray();
        $psychologistArray['verification_details'] = $verification;

        return Inertia::render('Admin/Psychologist/Edit', [
            'profile' => $loaded,
            'psychologist' => $psychologistArray,
            'specialisations' => Specialisation::query()->orderBy('name')->get(['id', 'name']),
            'expertises' => Expertise::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function destroy(PsychologistProfile $psychologistProfile): RedirectResponse
    {
        $psychologistProfile->delete();

        ActivityLogger::log(request()->user()->id ?? null, 'ADMIN', 'deleted_psychologist_profile', 'PsychologistProfile', $psychologistProfile->id, 'Psychologist profile deleted');

        if (request()->expectsJson()) {
            return response()->noContent();
        }

        return redirect()->route('psychologist-profiles.index');
    }

    public function approve(PsychologistProfile $psychologistProfile): HttpResponse
    {
        $psychologistProfile->update(['is_approved' => true]);

        return response()->json(['message' => 'Psychologist approved successfully']);
    }

    public function disapprove(PsychologistProfile $psychologistProfile): HttpResponse
    {
        $psychologistProfile->update(['is_approved' => false]);

        return response()->json(['message' => 'Psychologist disapproved successfully']);
    }

    /**
     * Normalize an array/string of expertise items coming from the client.
     * Accepts numeric ids, plain names, or option objects like { value, label }.
     * Creates missing expertise names via firstOrCreate and returns an array of ids.
     *
     * @param mixed $items
     * @return array<int>
     */
    private function resolveExpertiseItems($items): array
    {
        // If client sent a JSON string, decode it
        if (is_string($items) && $items !== '') {
            $decoded = json_decode($items, true);
            if (is_array($decoded)) {
                $items = $decoded;
            }
        }

        if (! is_array($items)) {
            return [];
        }

        $ids = [];
        foreach ($items as $it) {
            // numeric id
            if (is_numeric($it)) {
                $found = Expertise::find(intval($it));
                if ($found) $ids[] = $found->id;
                continue;
            }

            // option object/array
            if (is_array($it)) {
                $val = $it['value'] ?? null;
                $label = $it['label'] ?? null;

                if ($val !== null) {
                    if (is_numeric($val)) {
                        $found = Expertise::find(intval($val));
                        if ($found) { $ids[] = $found->id; continue; }
                    } elseif (is_string($val) && trim($val) !== '') {
                        $name = trim($val);
                        $rec = Expertise::firstOrCreate(['name' => $name]);
                        $ids[] = $rec->id;
                        continue;
                    }
                }

                if ($label && is_string($label) && trim($label) !== '') {
                    $rec = Expertise::firstOrCreate(['name' => trim($label)]);
                    $ids[] = $rec->id;
                    continue;
                }

                continue;
            }

            // plain string -> treat as name / tag
            if (is_string($it) && trim($it) !== '') {
                $rec = Expertise::firstOrCreate(['name' => trim($it)]);
                $ids[] = $rec->id;
            }
        }

        return array_values(array_unique(array_map('intval', $ids)));
    }

    public function updateVerification(Request $request, PsychologistProfile $psychologistProfile)
    {
        // If no verification details exist yet for this profile, require key fields
        $existingVerification = $psychologistProfile->verificationDetails;

        $rules = [
            'rib' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:255',
            'bank_account_name' => 'nullable|string|max:255',
            'rib_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB
            'identity_type' => 'nullable|string|max:255',
            'identity_number' => 'nullable|string|max:255',
            'identity_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB
            'diploma_files' => 'nullable|array',
            'diploma_files.*' => 'file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB
            'verification_status' => 'nullable|in:pending,approved,rejected',
            'rejection_reason' => 'nullable|string',
        ];

        if (! $existingVerification) {
            // Creating new verification record: require the main fields and files
            $rules['rib'] = 'required|string|max:255';
            $rules['bank_name'] = 'required|string|max:255';
            $rules['bank_account_number'] = 'required|string|max:255';
            $rules['bank_account_name'] = 'required|string|max:255';
            // Require at least a RIB file (file upload) when creating
            $rules['rib_file'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:5120';
            $rules['identity_type'] = 'required|string|max:255';
            $rules['identity_number'] = 'required|string|max:255';
            $rules['identity_file'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:5120';
            // Require at least one diploma when creating verification
            $rules['diploma_files'] = 'required|array|min:1';
        }

        $request->validate($rules);

        $verificationDetails = $psychologistProfile->verificationDetails;

        if (!$verificationDetails) {
            // Create new verification details if they don't exist
            $verificationDetails = new PsychologistVerificationDetails([
                'psychologist_profile_id' => $psychologistProfile->id,
                'verification_status' => 'pending',
            ]);
        }

        // Update basic fields
        $verificationDetails->fill([
            'rib' => $request->rib,
            'bank_name' => $request->bank_name,
            'bank_account_number' => $request->bank_account_number,
            'bank_account_name' => $request->bank_account_name,
            'identity_type' => $request->identity_type,
            'identity_number' => $request->identity_number,
        ]);

        // Apply verification status and optional rejection reason if provided
        if ($request->filled('verification_status')) {
            $verificationDetails->verification_status = $request->input('verification_status');
            if ($request->input('verification_status') === 'rejected') {
                $verificationDetails->rejection_reason = $request->input('rejection_reason');
            } else {
                // clear rejection reason when approving or setting back to pending
                $verificationDetails->rejection_reason = null;
            }
        }

        // Upload RIB file if provided
        if ($request->hasFile('rib_file')) {
            try {
                // Delete old file if exists
                if ($verificationDetails->rib_file_url) {
                    $publicId = self::getCloudinaryPublicId($verificationDetails->rib_file_url);
                    if ($publicId) {
                        Cloudinary::destroy($publicId);
                    }
                }

                $uploadedFile = Cloudinary::upload($request->file('rib_file')->getRealPath(), [
                    'folder' => 'psychologist_verifications/rib',
                    'public_id' => 'rib_' . $psychologistProfile->id . '_' . time(),
                ]);
                $verificationDetails->rib_file_url = $uploadedFile->getSecurePath();
            } catch (\Throwable $e) {
                Log::warning('Cloudinary RIB upload failed: ' . $e->getMessage());
                return back()->withErrors(['rib_file' => 'Failed to upload RIB file. Please try again.']);
            }
        }

        // Upload identity file if provided
        if ($request->hasFile('identity_file')) {
            try {
                // Delete old file if exists
                if ($verificationDetails->identity_file_url) {
                    $publicId = self::getCloudinaryPublicId($verificationDetails->identity_file_url);
                    if ($publicId) {
                        Cloudinary::destroy($publicId);
                    }
                }

                $uploadedFile = Cloudinary::upload($request->file('identity_file')->getRealPath(), [
                    'folder' => 'psychologist_verifications/identity',
                    'public_id' => 'identity_' . $psychologistProfile->id . '_' . time(),
                ]);
                $verificationDetails->identity_file_url = $uploadedFile->getSecurePath();
            } catch (\Throwable $e) {
                Log::warning('Cloudinary identity upload failed: ' . $e->getMessage());
                return back()->withErrors(['identity_file' => 'Failed to upload identity file. Please try again.']);
            }
        }

        $verificationDetails->save();

        // Handle diploma files
        if ($request->hasFile('diploma_files')) {
            // Delete existing diplomas
            foreach ($verificationDetails->diplomas as $diploma) {
                if ($diploma->file_url) {
                    $publicId = self::getCloudinaryPublicId($diploma->file_url);
                    if ($publicId) {
                        Cloudinary::destroy($publicId);
                    }
                }
                $diploma->delete();
            }

            // Upload new diploma files
            foreach ($request->file('diploma_files') as $file) {
                try {
                    $uploadedFile = Cloudinary::upload($file->getRealPath(), [
                        'folder' => 'psychologist_verifications/diplomas',
                        'public_id' => 'diploma_' . $verificationDetails->id . '_' . time() . '_' . uniqid(),
                    ]);
                    $url = $uploadedFile->getSecurePath();
                    if ($url) {
                        $verificationDetails->diplomas()->create(['file_url' => $url]);
                    }
                } catch (\Throwable $e) {
                    Log::warning('Cloudinary diploma upload failed: ' . $e->getMessage());
                }
            }
        }

        return response()->json([
            'message' => 'Verification details updated successfully.',
            'verification' => $verificationDetails->load('diplomas')
        ]);
    }

    /**
     * Return verification details as JSON for admin UI.
     */
    public function showVerification(PsychologistProfile $psychologistProfile)
    {
        $verification = $psychologistProfile->verificationDetails;
        if (! $verification) {
            return response()->json(['verification' => null]);
        }

        $v = $verification->load('diplomas')->toArray();
        if (isset($v['identity_file_url']) && $v['identity_file_url']) {
            $v['identity_files_urls'] = [$v['identity_file_url']];
        } else {
            $v['identity_files_urls'] = [];
        }
        if (isset($v['diplomas']) && is_array($v['diplomas'])) {
            $v['diploma_files_urls'] = array_map(function ($d) { return $d['file_url']; }, $v['diplomas']);
        } else {
            $v['diploma_files_urls'] = [];
        }

        return response()->json(['verification' => $v]);
    }
}
