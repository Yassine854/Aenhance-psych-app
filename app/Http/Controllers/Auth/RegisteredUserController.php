<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PatientProfile;
use App\Models\PsychologistProfile;
use App\Models\Specialisation;
use App\Models\Expertise;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request): Response
    {
        $redirect = $request->query('redirect');
        if (is_string($redirect) && $redirect !== '') {
            $request->session()->put('url.intended', $redirect);
        }

        return Inertia::render('Auth/Register', [
            'specialisations' => Specialisation::query()
                ->orderBy('name')
                ->get(['id', 'name']),
            'expertises' => Expertise::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $role = strtoupper((string) $request->input('role', 'PATIENT'));

        // If sent as FormData, arrays may arrive as JSON strings.
        if ($role === 'PSYCHOLOGIST') {
            $rawLang = $request->input('languages');
            if (is_string($rawLang) && $rawLang !== '') {
                $decoded = json_decode($rawLang, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $request->merge(['languages' => $decoded]);
                }
            }
        }

        $validated = $request->validate([
            'role' => ['required', 'in:PATIENT,PSYCHOLOGIST'],

            // Account fields
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],

            // Patient profile fields
            'patient_first_name' => [$role === 'PATIENT' ? 'required' : 'nullable', 'string', 'max:255'],
            'patient_last_name' => [$role === 'PATIENT' ? 'required' : 'nullable', 'string', 'max:255'],
            'patient_date_of_birth' => [$role === 'PATIENT' ? 'required' : 'nullable', 'date'],
            'patient_gender' => ['nullable', 'string', 'max:50'],
            'patient_country' => ['nullable', 'string', 'max:255'],
            'patient_city' => ['nullable', 'string', 'max:255'],
            'patient_phone' => ['nullable', 'string', 'max:50'],
            'patient_country_code' => ['nullable', 'string', 'max:10'],
            'patient_profile_image' => ['nullable', 'file', 'image', 'max:1024'],

            // Psychologist profile fields
            'first_name' => [$role === 'PSYCHOLOGIST' ? 'required' : 'nullable', 'string', 'max:255'],
            'last_name' => [$role === 'PSYCHOLOGIST' ? 'required' : 'nullable', 'string', 'max:255'],
            'languages' => [$role === 'PSYCHOLOGIST' ? 'required' : 'nullable', 'array', 'min:1'],
            'languages.*' => ['required', 'string', 'distinct', 'in:english,french,arabic'],
            'specialisation_ids' => [$role === 'PSYCHOLOGIST' ? 'required' : 'nullable', 'array', 'min:1'],
            'specialisation_ids.*' => ['integer', 'distinct', 'exists:specialisations,id'],
            'expertise_ids' => [$role === 'PSYCHOLOGIST' ? 'nullable' : 'nullable', 'array'],
            'expertise_ids.*' => ['integer', 'distinct', 'exists:expertises,id'],
            'date_of_birth' => [$role === 'PSYCHOLOGIST' ? 'required' : 'nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:50'],
            'country' => [$role === 'PSYCHOLOGIST' ? 'required' : 'nullable', 'string', 'max:255'],
            'city' => [$role === 'PSYCHOLOGIST' ? 'required' : 'nullable', 'string', 'max:255'],
            'phone' => [$role === 'PSYCHOLOGIST' ? 'required' : 'nullable', 'string', 'max:50'],
            'country_code' => ['nullable', 'string', 'max:10'],
            'address' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
            'price_per_session' => [$role === 'PSYCHOLOGIST' ? 'required' : 'nullable', 'numeric', 'min:0'],
            'profile_image' => ['nullable', 'file', 'image', 'max:1024'],
            'diploma_files' => [$role === 'PSYCHOLOGIST' ? 'required' : 'nullable', 'array', 'min:1'],
            'diploma_files.*' => ['file', 'mimes:pdf', 'max:1024'],
            // CIN removed
            'cv_file' => [$role === 'PSYCHOLOGIST' ? 'required' : 'nullable', 'file', 'mimes:pdf', 'max:1024'],

            // Psychologist availability (JSON string from the frontend)
            'availabilities' => [$role === 'PSYCHOLOGIST' ? 'required' : 'nullable', 'string'],
        ]);

        $availabilities = [];
        if ($role === 'PSYCHOLOGIST') {
            $raw = $request->input('availabilities');
            if (is_string($raw)) {
                $decoded = json_decode($raw, true);
                if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                    throw ValidationException::withMessages([
                        'availabilities' => ['Invalid availability payload.'],
                    ]);
                }
                $availabilities = $decoded;
            }

            if (empty($availabilities)) {
                throw ValidationException::withMessages([
                    'availabilities' => ['Please add at least one availability slot.'],
                ]);
            }

            // Validate shape + overlaps
            $byDay = [];
            foreach ($availabilities as $idx => $slot) {
                if (!is_array($slot)) {
                    throw ValidationException::withMessages([
                        'availabilities' => ['Invalid slot format.'],
                    ]);
                }

                $day = $slot['day_of_week'] ?? null;
                $start = $slot['start_time'] ?? null;
                $end = $slot['end_time'] ?? null;

                if (!is_int($day) && !(is_string($day) && ctype_digit($day))) {
                    throw ValidationException::withMessages([
                        'availabilities' => ['Each slot must have a valid day_of_week.'],
                    ]);
                }
                $day = (int) $day;
                if ($day < 0 || $day > 6) {
                    throw ValidationException::withMessages([
                        'availabilities' => ['day_of_week must be between 0 and 6.'],
                    ]);
                }

                if (!is_string($start) || !preg_match('/^\d{2}:\d{2}$/', $start)) {
                    throw ValidationException::withMessages([
                        'availabilities' => ['Each slot must have a valid start_time (HH:MM).'],
                    ]);
                }
                if (!is_string($end) || !preg_match('/^\d{2}:\d{2}$/', $end)) {
                    throw ValidationException::withMessages([
                        'availabilities' => ['Each slot must have a valid end_time (HH:MM).'],
                    ]);
                }

                try {
                    $startM = Carbon::createFromFormat('H:i', $start);
                    $endM = Carbon::createFromFormat('H:i', $end);
                } catch (\Throwable $e) {
                    throw ValidationException::withMessages([
                        'availabilities' => ['Invalid time format in availability slots.'],
                    ]);
                }

                if ($endM->lessThanOrEqualTo($startM)) {
                    throw ValidationException::withMessages([
                        'availabilities' => ['End time must be after start time.'],
                    ]);
                }

                $byDay[$day] ??= [];
                $byDay[$day][] = [
                    'start' => (int) $startM->format('H') * 60 + (int) $startM->format('i'),
                    'end' => (int) $endM->format('H') * 60 + (int) $endM->format('i'),
                    'start_time' => $start,
                    'end_time' => $end,
                    'day_of_week' => $day,
                ];
            }

            foreach ($byDay as $day => $slots) {
                usort($slots, fn ($a, $b) => $a['start'] <=> $b['start']);
                for ($i = 1; $i < count($slots); $i++) {
                    if ($slots[$i]['start'] < $slots[$i - 1]['end']) {
                        throw ValidationException::withMessages([
                            'availabilities' => ['Availability slots overlap. Please adjust times.'],
                        ]);
                    }
                }
            }

            // Normalize to the DB shape expected by the relationship.
            $availabilities = array_map(function ($slot) {
                return [
                    'day_of_week' => (int) $slot['day_of_week'],
                    'start_time' => (string) $slot['start_time'],
                    'end_time' => (string) $slot['end_time'],
                ];
            }, $availabilities);
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        if ($role === 'PATIENT') {
            $profileImageUrl = null;
            if ($request->hasFile('patient_profile_image')) {
                try {
                    $uploaded = Cloudinary::upload($request->file('patient_profile_image')->getRealPath(), [
                        'folder' => 'patient_profiles',
                        'transformation' => ['width' => 800, 'height' => 800, 'crop' => 'limit'],
                    ]);
                    $profileImageUrl = method_exists($uploaded, 'getSecurePath')
                        ? $uploaded->getSecurePath()
                        : (method_exists($uploaded, 'getPath') ? $uploaded->getPath() : null);
                } catch (\Throwable $e) {
                    $path = $request->file('patient_profile_image')->store('patient_profiles', 'public');
                    $profileImageUrl = Storage::url($path);
                }
            }

            PatientProfile::create([
                'user_id' => $user->id,
                'first_name' => $validated['patient_first_name'],
                'last_name' => $validated['patient_last_name'],
                'date_of_birth' => $validated['patient_date_of_birth'],
                'gender' => $validated['patient_gender'] ?? null,
                'country' => $validated['patient_country'] ?? null,
                'city' => $validated['patient_city'] ?? null,
                'phone' => $validated['patient_phone'] ?? null,
                'country_code' => $validated['patient_country_code'] ?? null,
                'profile_image_url' => $profileImageUrl,
            ]);
        }

        if ($role === 'PSYCHOLOGIST') {
            $profileImageUrl = null;
            if ($request->hasFile('profile_image')) {
                try {
                    $uploaded = Cloudinary::upload($request->file('profile_image')->getRealPath(), [
                        'folder' => 'psychologist_profiles',
                        'transformation' => ['width' => 800, 'height' => 800, 'crop' => 'limit'],
                    ]);
                    $profileImageUrl = method_exists($uploaded, 'getSecurePath')
                        ? $uploaded->getSecurePath()
                        : (method_exists($uploaded, 'getPath') ? $uploaded->getPath() : null);
                } catch (\Throwable $e) {
                    $path = $request->file('profile_image')->store('psychologist_profiles', 'public');
                    $profileImageUrl = Storage::url($path);
                }
            }

            // Handle multiple diplomas
            $diplomaUploads = [];
            if ($request->hasFile('diploma_files')) {
                $files = $request->file('diploma_files');
                if (!is_array($files)) $files = [$files];
                foreach ($files as $file) {
                    try {
                        $uploaded = Cloudinary::uploadFile($file->getRealPath(), [
                            'folder' => 'psychologist_profiles/diplomas',
                            'resource_type' => 'raw',
                        ]);
                        $url = $uploaded->getSecurePath();
                        $url = str_replace('/image/upload/', '/raw/upload/', $url);
                        $diplomaUploads[] = [
                            'file_url' => $url,
                        ];
                    } catch (\Throwable $e) {
                        $path = $file->store('psychologist_profiles/diplomas', 'public');
                        $url = Storage::url($path);
                        $diplomaUploads[] = [
                            'file_url' => $url,
                        ];
                    }
                }
            }

            // CIN field removed - not collecting CIN on registration

            $cvUrl = '';
            if ($request->hasFile('cv_file')) {
                try {
                    $uploaded = Cloudinary::uploadFile($request->file('cv_file')->getRealPath(), [
                        'folder' => 'psychologist_profiles/cvs',
                        'resource_type' => 'raw',
                    ]);
                    $cvUrl = $uploaded->getSecurePath();
                    $cvUrl = str_replace('/image/upload/', '/raw/upload/', $cvUrl);
                } catch (\Throwable $e) {
                    $path = $request->file('cv_file')->store('psychologist_profiles/cvs', 'public');
                    $cvUrl = Storage::url($path);
                }
            }

            $profile = PsychologistProfile::create([
                'user_id' => $user->id,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'languages' => $validated['languages'],
                'cv' => $cvUrl,
                'gender' => $validated['gender'] ?? null,
                'country' => $validated['country'],
                'city' => $validated['city'],
                'phone' => $validated['phone'],
                'country_code' => $validated['country_code'] ?? null,
                'address' => $validated['address'] ?? null,
                'date_of_birth' => $validated['date_of_birth'],
                'bio' => $validated['bio'] ?? null,
                'price_per_session' => $validated['price_per_session'],
                'is_approved' => false,
                'profile_image_url' => $profileImageUrl,
            ]);

            // Attach uploaded diplomas
            if (!empty($diplomaUploads)) {
                $profile->diplomas()->createMany($diplomaUploads);
            }

            $specialisationIds = array_values(array_unique($validated['specialisation_ids'] ?? []));
            if (! empty($specialisationIds)) {
                $profile->specialisations()->sync($specialisationIds);
            }

            $expertiseIds = array_values(array_unique($validated['expertise_ids'] ?? []));
            if (! empty($expertiseIds)) {
                $profile->expertises()->sync($expertiseIds);
            }

            if (!empty($availabilities)) {
                $profile->availabilities()->createMany($availabilities);
            }
        }

        event(new Registered($user));

        Auth::login($user);

        if (method_exists($user, 'isPatient') && $user->isPatient()) {
            return redirect()->intended('/');
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }
}
