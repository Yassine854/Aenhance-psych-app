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
use App\Http\Controllers\Psychologist\PsychologistSelfProfileController;
use App\Http\Controllers\Admin\LogsController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\SpecialisationController;
use App\Http\Controllers\ExpertiseController;
use App\Http\Controllers\AppointmentSessionController;
use App\Http\Controllers\SessionRatingController;
use App\Http\Controllers\AppointmentSessionNoteController;
use App\Http\Controllers\Psychologist\PsychologistPatientController;
use App\Http\Controllers\Psychologist\PsychologistPayoutController;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AppFeeController;
use App\Http\Controllers\Admin\PaymentsController;

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
    $user = Auth::user();
    $props = [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'authUser' => $user,
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ];

    if ($user && $user->isPsychologist()) {
        $props['psychologist'] = $user->psychologistProfile;
    }

    return Inertia::render('Welcome', $props);
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
            return redirect()->route('home');
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

// Reports (patients can report psychologists)
Route::post('/reports', [App\Http\Controllers\ReportController::class, 'store'])->name('reports.store')->middleware('auth');


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

    // Payments (Admin)
    Route::get('/admin/payments', [PaymentsController::class, 'index'])->name('admin.payments.index');
    Route::get('/admin/payments/{payment}', [PaymentsController::class, 'show'])->name('admin.payments.show');
    Route::patch('/admin/payments/{payment}', [PaymentsController::class, 'updateStatus'])->name('admin.payments.update');

    // Payouts (Admin)
    Route::get('/admin/payouts', [App\Http\Controllers\Admin\PayoutsController::class, 'index'])->name('admin.payouts.index');
    Route::get('/admin/payouts/{payout}', [App\Http\Controllers\Admin\PayoutsController::class, 'show'])->name('admin.payouts.show');
    Route::patch('/admin/payouts/{payout}', [App\Http\Controllers\Admin\PayoutsController::class, 'updateStatus'])->name('admin.payouts.update');
    Route::patch('/admin/payouts', [App\Http\Controllers\Admin\PayoutsController::class, 'bulkUpdate'])->name('admin.payouts.bulk_update');

    // Specialisations (Admin)
    Route::get('/specialisations', [SpecialisationController::class, 'index'])->name('specialisations.index');
    Route::post('/specialisations', [SpecialisationController::class, 'store'])->name('specialisations.store');
    Route::patch('/specialisations/{specialisation}', [SpecialisationController::class, 'update'])->name('specialisations.update');
    Route::delete('/specialisations/{specialisation}', [SpecialisationController::class, 'destroy'])->name('specialisations.destroy');

    // Expertises (Admin)
    Route::get('/expertises', [ExpertiseController::class, 'index'])->name('expertises.index');
    Route::post('/expertises', [ExpertiseController::class, 'store'])->name('expertises.store');
    Route::patch('/expertises/{expertise}', [ExpertiseController::class, 'update'])->name('expertises.update');
    Route::delete('/expertises/{expertise}', [ExpertiseController::class, 'destroy'])->name('expertises.destroy');

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

    // Appointment session tracking (used by WebRTC call UI)
    Route::get('/appointments/{appointment}/session', [AppointmentSessionController::class, 'show'])
        ->name('appointments.session.show');
    Route::post('/appointments/{appointment}/session/join', [AppointmentSessionController::class, 'join'])
        ->name('appointments.session.join');
    Route::post('/appointments/{appointment}/session/leave', [AppointmentSessionController::class, 'leave'])
        ->name('appointments.session.leave');
    Route::post('/appointments/{appointment}/session/end', [AppointmentSessionController::class, 'end'])
        ->name('appointments.session.end');
    // Psychologist patients UI (Inertia page)
    Route::get('/psychologist/patients', [PsychologistPatientController::class, 'index'])
        ->name('psychologist.patients.index');

    Route::get('/psychologist/payouts', [PsychologistPayoutController::class, 'index'])
        ->name('psychologist.payouts.index');

    Route::get('/psychologist/patients/{patient}/notes', [PsychologistPatientController::class, 'notes'])
        ->name('psychologist.patients.notes');

    // Legacy/api endpoint returning a simple patient list (kept for compatibility)
    Route::get('/psychologist/patients/api', [PsychologistController::class, 'patients']);

    // Psychologist profile CRUD (explicit routes)
    Route::get('/psychologist-profiles', [PsychologistProfileController::class, 'index'])->name('psychologist-profiles.index');
    Route::get('/psychologist-profiles/create', [PsychologistProfileController::class, 'create'])->name('psychologist-profiles.create');
    Route::post('/psychologist-profiles', [PsychologistProfileController::class, 'store'])->name('psychologist-profiles.store');
    Route::get('/psychologist-profiles/{psychologist_profile}', [PsychologistProfileController::class, 'show'])->name('psychologist-profiles.show');
    Route::get('/psychologist-profiles/{psychologist_profile}/edit', [PsychologistProfileController::class, 'edit'])->name('psychologist-profiles.edit');
    Route::patch('/psychologist-profiles/{psychologist_profile}/approve', [PsychologistProfileController::class, 'approve'])->name('psychologist-profiles.approve');
    Route::patch('/psychologist-profiles/{psychologist_profile}/disapprove', [PsychologistProfileController::class, 'disapprove'])->name('psychologist-profiles.disapprove');
    Route::match(['put','patch'], '/psychologist-profiles/{psychologist_profile}', [PsychologistProfileController::class, 'update'])->name('psychologist-profiles.update');
    Route::delete('/psychologist-profiles/{psychologist_profile}', [PsychologistProfileController::class, 'destroy'])->name('psychologist-profiles.destroy');

    // Psychologist verification admin update
    Route::match(['put','patch'], '/psychologist-profiles/{psychologist_profile}/verification', [PsychologistProfileController::class, 'updateVerification'])->name('psychologist.verification.admin.update');
    // Psychologist verification admin show (JSON) - used by admin Edit modal to fetch existing verification details
    Route::get('/psychologist-profiles/{psychologist_profile}/verification', [PsychologistProfileController::class, 'showVerification'])->name('psychologist.verification.admin.show');

    // Logs (admin)
    Route::get('/admin/logs/appointments', [LogsController::class, 'appointmentsIndex'])->name('admin.logs.appointments.index');
    Route::get('/admin/logs/appointments/{log}', [LogsController::class, 'appointmentsShow'])->name('admin.logs.appointments.show');
    Route::get('/admin/logs/appointments/{log}/related', [LogsController::class, 'appointmentsRelated'])->name('admin.logs.appointments.related');
    // Session logs
    Route::get('/admin/logs/sessions', [LogsController::class, 'sessionsIndex'])->name('admin.logs.sessions.index');
    Route::get('/admin/logs/sessions/{log}', [LogsController::class, 'sessionsShow'])->name('admin.logs.sessions.show');
    Route::get('/admin/logs/sessions/{log}/related', [LogsController::class, 'sessionsRelated'])->name('admin.logs.sessions.related');
    // placeholder psychologists logs (can be implemented similarly)
    Route::get('/admin/logs/psychologists', [LogsController::class, 'psychologistsIndex'])->name('admin.logs.psychologists.index');
    Route::get('/admin/logs/psychologists/{log}', [LogsController::class, 'psychologistsShow'])->name('admin.logs.psychologists.show');
    Route::get('/admin/logs/patients', [LogsController::class, 'patientsIndex'])->name('admin.logs.patients.index');
    Route::get('/admin/logs/patients/{log}', [LogsController::class, 'patientsShow'])->name('admin.logs.patients.show');

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
    Route::get('/appointments/pending-count', [AppointmentController::class, 'pendingCount'])->name('appointments.pendingCount');
    Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
    // ClickToPay payment start (redirects to gateway)
    Route::post('/appointments/{appointment}/pay', [AppointmentController::class, 'startClickToPay'])
        ->name('appointments.pay');
    // ClickToPay return/fail callbacks (user is redirected back here by the gateway)
    Route::get('/payments/clictopay/return/{appointment}', [AppointmentController::class, 'clicToPayReturn'])
        ->name('payments.clictopay.return');
    Route::get('/payments/clictopay/fail/{appointment}', [AppointmentController::class, 'clicToPayFail'])
        ->name('payments.clictopay.fail');
    Route::patch('/appointments/{appointment}', [AppointmentController::class, 'update'])->name('appointments.update');
    Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy'])->name('appointments.destroy');

    // Patient appointments UI
    Route::get('/patient/appointments', [AppointmentController::class, 'patientIndex'])
        ->name('patient.appointments');

    // Session ratings (patient submits rating after completed session)
    Route::post('/session-ratings', [SessionRatingController::class, 'store'])->name('session-ratings.store');

    // Appointment session notes (psychologist creates notes after session)
    Route::post('/appointment-session-notes', [AppointmentSessionNoteController::class, 'store'])
    ->name('appointment-session-notes.store');
    Route::get('/appointments/{appointment}/session-note', [AppointmentSessionNoteController::class, 'showByAppointment'])
    ->name('appointment-session-note.show');
    Route::patch('/appointment-session-notes/{note}', [AppointmentSessionNoteController::class, 'update'])
    ->name('appointment-session-notes.update');

    // Patient booking flow
    Route::get('/appointments/book/{psychologist_profile}', [AppointmentController::class, 'book'])
        ->name('appointments.book');

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

    // Psychologist account settings (psychologist-facing page)
    Route::get('/psychologist/account', function (Request $request) {
        $user = $request->user();
        if (! $user || ! method_exists($user, 'isPsychologist') || ! $user->isPsychologist()) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Psychologist/Account', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'authUser' => $user,
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    })->name('psychologist.account');

    // Psychologist profile (psychologist-facing page)
    Route::get('/psychologist/profile/edit', [PsychologistSelfProfileController::class, 'edit'])->name('psychologist.profile.self');
    Route::post('/psychologist/profile/edit', [PsychologistSelfProfileController::class, 'update'])->name('psychologist.profile.self.update');

    // Psychologist availabilities (view and edit separately from profile)
    Route::get('/psychologist/availabilities', [PsychologistSelfProfileController::class, 'availabilities'])->name('psychologist.availabilities');
    Route::patch('/psychologist/availabilities', [PsychologistSelfProfileController::class, 'updateAvailabilities'])->name('psychologist.availabilities.update');

    // Psychologist verification
    Route::get('/psychologist/verification/create', [PsychologistSelfProfileController::class, 'createVerification'])->name('psychologist.verification.create');
    Route::post('/psychologist/verification/store', [PsychologistSelfProfileController::class, 'storeVerification'])->name('psychologist.verification.store');

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

// App fees management (web resource, auth protected)
Route::resource('app-fees', AppFeeController::class)->middleware('auth');
