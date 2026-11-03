<?php

// Isolated regression checks runnable without PHPUnit: php tests/security-check.php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

function check(bool $condition, string $message): void
{
    if (! $condition) {
        throw new RuntimeException($message);
    }
}

foreach (['/dashboard' => true, '/appointments/3/video-call?x=1' => true, 'https://evil.example' => false, '//evil.example' => false, '/\\evil.example' => false, "/\nevil.example" => false] as $target => $allowed) {
    $request = Illuminate\Http\Request::create('/login', 'GET', ['redirect' => $target]);
    $session = new Illuminate\Session\Store('security-test', new Illuminate\Session\ArraySessionHandler(120));
    $session->start();
    $request->setLaravelSession($session);
    (new App\Http\Controllers\Auth\AuthenticatedSessionController)->create($request);
    check($session->get('url.intended') === ($allowed ? $target : null), 'Unsafe login redirect accepted');
}

$csrf = new App\Http\Middleware\VerifyCsrfToken($app, $app['encrypter']);
foreach (['/appointments/1/session/join', '/appointments/1/session/leave', '/appointments/1/session/end', '/session-ratings', '/appointment-session-notes'] as $path) {
    $request = Illuminate\Http\Request::create($path, 'POST');
    $request->setLaravelSession($session);
    try {
        $csrf->handle($request, fn () => response('OK'));
        throw new RuntimeException('Missing CSRF token was accepted: '.$path);
    } catch (Illuminate\Session\TokenMismatchException $e) {
        // Expected.
    }
    $request->headers->set('X-CSRF-TOKEN', $session->token());
    check($csrf->handle($request, fn () => response('OK'))->getStatusCode() === 200, 'Valid CSRF token rejected');
}

$user = new App\Models\User(['role' => 'PATIENT', 'is_active' => 1]);
$user->id = 42;
$request = Illuminate\Http\Request::create('/');
$request->setUserResolver(fn () => $user);
$response = (new App\Http\Middleware\SecurityHeaders)->handle($request, fn () => response('OK'));
check($response->headers->get('X-Content-Type-Options') === 'nosniff', 'Missing nosniff header');
check(str_contains($response->headers->get('Cache-Control'), 'no-store'), 'Private response is cacheable');
Illuminate\Support\Facades\Auth::guard()->setUser($user);
$role = new App\Http\Middleware\RoleMiddleware;
check($role->handle($request, fn () => response('OK'), 'PATIENT')->getStatusCode() === 200, 'Active user denied');
$user->is_active = 0;
try {
    $role->handle($request, fn () => response('OK'), 'PATIENT');
    throw new RuntimeException('Blocked user accepted');
} catch (Symfony\Component\HttpKernel\Exception\HttpException $e) {
    check($e->getStatusCode() === 403, 'Unexpected blocked-user response');
}

$routes = app('router')->getRoutes();
check($routes->match(Illuminate\Http\Request::create('/register', 'POST'))->gatherMiddleware() !== [], 'Registration middleware missing');
foreach ($routes as $route) {
    check($route->uri() !== 'api/psychologist-profiles/test', 'Private profile debug route is exposed');
}
// Only an in-memory SQLite connection is used below; no existing database is read or changed.
config(['database.default' => 'security_test', 'database.connections.security_test' => [
    'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
]]);
Illuminate\Support\Facades\Schema::create('appointment_sessions', function ($table) {
    $table->id();
    $table->unsignedBigInteger('appointment_id');
    $table->string('room_id');
    $table->string('status');
    $table->timestamp('ended_at')->nullable();
});
Illuminate\Support\Facades\DB::table('appointment_sessions')->insert([
    'appointment_id' => 7, 'room_id' => 'signed-room', 'status' => 'active',
]);
config(['signaling.secret' => 'test-only-signaling-secret-32-characters']);
$appointment = new App\Models\Appointment(['patient_id' => 42, 'psychologist_id' => 43, 'status' => 'confirmed']);
$appointment->id = 7;
$tokenController = new App\Http\Controllers\SignalingTokenController;
foreach ([42 => 'PATIENT', 43 => 'PSYCHOLOGIST'] as $id => $roleName) {
    $user = new App\Models\User(['name' => 'Test participant', 'role' => $roleName, 'is_active' => 1]);
    $user->id = $id;
    $request = Illuminate\Http\Request::create('/');
    $request->setUserResolver(fn () => $user);
    $response = $tokenController($request, $appointment);
    [$payload, $signature] = explode('.', $response->getData(true)['token']);
    check(hash_equals(hash_hmac('sha256', $payload, config('signaling.secret')), $signature), 'Invalid signature');
    $claims = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);
    check($claims['roomId'] === 'signed-room' && $claims['role'] === strtolower($roleName), 'Wrong signed identity');
    check(abs($claims['exp'] - time() - 300) <= 2, 'Wrong expiry');
}
$appointment->patient_id = 99;
$appointment->psychologist_id = 98;
try {
    $tokenController($request, $appointment);
    throw new RuntimeException('Unrelated participant received a token');
} catch (Symfony\Component\HttpKernel\Exception\HttpException $e) {
    check($e->getStatusCode() === 403, 'Wrong authorization error');
}
echo "PASS: redirects, CSRF, response headers, blocked users, removed debug route, participant-only signaling tokens\n";
