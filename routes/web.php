<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PsychologistController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\PsychologistProfileController;
use Illuminate\Foundation\Application;
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
            return Inertia::render('Patient/Dashboard');
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

    // Psychologist routes
    Route::get('/psychologist/appointments', [PsychologistController::class, 'appointments']);
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

    // Appointment routes (patient + psychologist + admin)
    Route::get('/appointments', [AppointmentController::class, 'index']);
    Route::post('/appointments', [AppointmentController::class, 'store']);
    Route::patch('/appointments/{appointment}', [AppointmentController::class, 'update']);
    Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy']);

    // Psychologist self profile (for logged-in psychologist)
    Route::get('/psychologist/profile', [PsychologistProfileController::class, 'editSelf'])->name('psychologist.profile.edit');
    Route::post('/psychologist/profile', [PsychologistProfileController::class, 'storeSelf'])->name('psychologist.profile.store');
    Route::match(['post', 'put', 'patch'], '/psychologist/profile/update', [PsychologistProfileController::class, 'updateSelf'])->name('psychologist.profile.update');

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
