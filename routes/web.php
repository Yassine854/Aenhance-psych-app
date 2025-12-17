<?php

use App\Http\Controllers\ProfileController;
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
    if ($user && $user->role === 'admin') {
        return Inertia::render('Admin/Dashboard');
    }
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
    Route::patch('/users/{user}/deactivate', [UserController::class, 'deactivate']);
    Route::patch('/users/{user}/activate', [UserController::class, 'activate']);

    // Psychologist routes
    Route::get('/psychologist/appointments', [PsychologistController::class, 'appointments']);
    Route::get('/psychologist/patients', [PsychologistController::class, 'patients']);

    // Appointment routes (patient + psychologist + admin)
    Route::get('/appointments', [AppointmentController::class, 'index']);
    Route::post('/appointments', [AppointmentController::class, 'store']);
    Route::patch('/appointments/{appointment}', [AppointmentController::class, 'update']);
    Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy']);

});







require __DIR__.'/auth.php';
