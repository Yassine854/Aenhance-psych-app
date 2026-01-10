<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PsychologistController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Psychologist\PsychologistAppointmentController;
use App\Http\Controllers\Admin\AdminAppointmentController;
use App\Http\Controllers\PsychologistProfileController;
use App\Http\Controllers\PatientProfileController;
use App\Http\Controllers\Patient\PatientSelfProfileController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\SpecialisationController;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'authUser' => Auth::user(),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('home');


//About page
Route::get('/telemental-health', function () {
    return Inertia::render('guest/about/TelementalHealth', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'authUser' => Auth::user(),
    ]);
})->name('telemental-health');

Route::get('/who-we-are', function () {
    return Inertia::render('guest/about/WhoWeAre', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'authUser' => Auth::user(),
    ]);
})->name('who-we-are');

Route::get('/terms-conditions', function () {
    return Inertia::render('guest/about/TermsConditions', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'authUser' => Auth::user(),
    ]);
})->name('terms-conditions');

Route::get('/privacy-protection', function () {
    return Inertia::render('guest/about/PrivacyProtection', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'authUser' => Auth::user(),
    ]);
})->name('privacy-protection');

//Support page 
Route::get('/faq', function () {
    return Inertia::render('guest/support/FAQ', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'authUser' => Auth::user(),
    ]);
})->name('faq');

Route::get('/how-it-works', function () {
    return Inertia::render('guest/support/HowItWorks', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'authUser' => Auth::user(),
    ]);
})->name('how-it-works');

// Services (public)
Route::get('/services', [ServicesController::class, 'index'])->name('services.index');
Route::get('/services/consultation', [ServicesController::class, 'consultation'])->name('services.consultation');


Route::get('/dashboard', function () {
    $user = Auth::user();
    if ($user) {
        if ($user->isAdmin()) {
            return Inertia::render('Admin/Dashboard');
        }
        if ($user->isPsychologist()) {
            return Inertia::render('Psychologist/Dashboard');
        }
        if ($user->isPatient()) {
            return redirect()->route('home');
        }
    }
    // Fallback for unexpected cases
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::middleware(['auth'])->group(function () {
// Admin routes
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::patch('/users/{user}', [UserController::class, 'update']);
    Route::patch('/users/{user}/deactivate', [UserController::class, 'deactivate']);
    Route::patch('/users/{user}/activate', [UserController::class, 'activate']);

    // Appointments (Admin)
    Route::get('/admin/appointments', [AdminAppointmentController::class, 'index'])->name('admin.appointments.index');
    Route::patch('/admin/appointments/{appointment}', [AdminAppointmentController::class, 'update'])->name('admin.appointments.update');

    // Specialisations (Admin)
    Route::get('/specialisations', [SpecialisationController::class, 'index'])->name('specialisations.index');
    Route::post('/specialisations', [SpecialisationController::class, 'store'])->name('specialisations.store');
    Route::patch('/specialisations/{specialisation}', [SpecialisationController::class, 'update'])->name('specialisations.update');
    Route::delete('/specialisations/{specialisation}', [SpecialisationController::class, 'destroy'])->name('specialisations.destroy');

    // Psychologist routes
    Route::get('/psychologist/appointments', [PsychologistAppointmentController::class, 'index'])
        ->name('psychologist.appointments.index');
    Route::patch('/psychologist/appointments/{appointment}/cancel', [PsychologistAppointmentController::class, 'cancel'])
        ->name('psychologist.appointments.cancel');

    // Video call (Jitsi)
    Route::post('/psychologist/appointments/{appointment}/video-call/start', [AppointmentController::class, 'startVideoCall'])
        ->name('psychologist.appointments.video_call.start');
    Route::get('/appointments/{appointment}/video-call', [AppointmentController::class, 'showVideoCall'])
        ->name('appointments.video_call.show');
    Route::get('/psychologist/patients', [PsychologistController::class, 'patients']);

    // Psychologist profile CRUD (explicit routes)
    Route::get('/psychologist-profiles', [PsychologistProfileController::class, 'index'])->name('psychologist-profiles.index');
    Route::get('/psychologist-profiles/create', [PsychologistProfileController::class, 'create'])->name('psychologist-profiles.create');
    Route::post('/psychologist-profiles', [PsychologistProfileController::class, 'store'])->name('psychologist-profiles.store');
    Route::get('/psychologist-profiles/{psychologist_profile}', [PsychologistProfileController::class, 'show'])->name('psychologist-profiles.show');
    Route::get('/psychologist-profiles/{psychologist_profile}/edit', [PsychologistProfileController::class, 'edit'])->name('psychologist-profiles.edit');
    Route::patch('/psychologist-profiles/{psychologist_profile}/approve', [PsychologistProfileController::class, 'approve'])->name('psychologist-profiles.approve');
    Route::match(['put','patch'], '/psychologist-profiles/{psychologist_profile}', [PsychologistProfileController::class, 'update'])->name('psychologist-profiles.update');
    Route::delete('/psychologist-profiles/{psychologist_profile}', [PsychologistProfileController::class, 'destroy'])->name('psychologist-profiles.destroy');

    // Patient profile CRUD (explicit routes)
    Route::get('/patient-profiles', [PatientProfileController::class, 'index'])->name('patient-profiles.index');
    Route::get('/patient-profiles/create', [PatientProfileController::class, 'create'])->name('patient-profiles.create');
    Route::post('/patient-profiles', [PatientProfileController::class, 'store'])->name('patient-profiles.store');
    Route::get('/patient-profiles/{patient_profile}', [PatientProfileController::class, 'show'])->name('patient-profiles.show');
    Route::get('/patient-profiles/{patient_profile}/edit', [PatientProfileController::class, 'edit'])->name('patient-profiles.edit');
    Route::match(['put','patch'], '/patient-profiles/{patient_profile}', [PatientProfileController::class, 'update'])->name('patient-profiles.update');
    Route::delete('/patient-profiles/{patient_profile}', [PatientProfileController::class, 'destroy'])->name('patient-profiles.destroy');

    // Appointment routes (patient + psychologist + admin)
    Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::patch('/appointments/{appointment}', [AppointmentController::class, 'update'])->name('appointments.update');
    Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy'])->name('appointments.destroy');

    // Patient appointments UI
    Route::get('/patient/appointments', [AppointmentController::class, 'patientIndex'])
        ->name('patient.appointments');

    // Patient booking flow
    Route::get('/appointments/book/{psychologist_profile}', [AppointmentController::class, 'book'])
        ->name('appointments.book');

    // Psychologist self profile (for logged-in psychologist)
    Route::get('/psychologist/profile', [PsychologistProfileController::class, 'editSelf'])->name('psychologist.profile.edit');
    Route::post('/psychologist/profile', [PsychologistProfileController::class, 'storeSelf'])->name('psychologist.profile.store');
    Route::match(['post', 'put', 'patch'], '/psychologist/profile/update', [PsychologistProfileController::class, 'updateSelf'])->name('psychologist.profile.update');

    // Patient account settings (patient-facing page)
    Route::get('/patient/account', function (Request $request) {
        $user = $request->user();
        if (! $user || ! method_exists($user, 'isPatient') || ! $user->isPatient()) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Patient/Account', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'authUser' => $user,
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    })->name('patient.account');

    // Patient profile (patient-facing page)
    Route::get('/patient/profile', [PatientSelfProfileController::class, 'edit'])->name('patient.profile');
    Route::post('/patient/profile', [PatientSelfProfileController::class, 'update'])->name('patient.profile.update');

});







Route::get('/test-cloudinary', function () {
    if (! app()->environment('local')) {
        abort(404);
    }

    try {
        // create a tiny local PNG without using GD (base64 of a 1x1 PNG)
        $tmp = sys_get_temp_dir().'/psych_debug.png';
        $pngBase64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVQI12NgYAAAAAMAAWgmWQ0AAAAASUVORK5CYII=';
        file_put_contents($tmp, base64_decode($pngBase64));

        $uploaded = \CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary::upload($tmp, [
            'folder' => 'psychologist_debug',
            'transformation' => ['width' => 10, 'height' => 10, 'crop' => 'limit'],
        ]);

        @unlink($tmp);

        $result = is_object($uploaded) ? (method_exists($uploaded, 'toArray') ? $uploaded->toArray() : json_decode(json_encode($uploaded), true)) : $uploaded;

        return response()->json(['success' => true, 'uploaded' => $result]);
    } catch (\Throwable $e) {
        return response()->json(['success' => false, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    }
});

require __DIR__.'/auth.php';
