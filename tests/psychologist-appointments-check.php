<?php

// Standalone integration check. The database is always isolated SQLite memory.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'session.driver' => 'array']);
Illuminate\Support\Facades\DB::purge('sqlite');
$db = Illuminate\Support\Facades\DB::connection('sqlite');
$db->getPdo()->sqliteCreateFunction('CONCAT', fn (...$parts) => implode('', $parts));
$schema = $db->getSchemaBuilder();
$schema->create('users', function ($table) { $table->id(); $table->string('name'); $table->string('role'); });
$schema->create('appointments', function ($table) {
    $table->id(); $table->integer('psychologist_id'); $table->integer('patient_id');
    $table->string('status'); $table->string('booking_for')->default('self');
    $table->dateTime('scheduled_start'); $table->dateTime('scheduled_end');
});
$schema->create('appointment_beneficiaries', function ($table) {
    $table->id(); $table->integer('appointment_id'); $table->string('first_name'); $table->string('last_name');
    $table->date('date_of_birth')->nullable(); $table->string('gender')->nullable(); $table->string('relationship_to_patient')->nullable();
});
$schema->create('appointment_sessions', function ($table) {
    $table->id(); $table->integer('appointment_id'); $table->string('room_id'); $table->string('status'); $table->dateTime('started_at')->nullable();
});
$db->table('users')->insert([['id' => 1, 'name' => 'Psychologist One', 'role' => 'PSYCHOLOGIST'], ['id' => 2, 'name' => 'Amine Patient', 'role' => 'PATIENT']]);
foreach (array_merge(array_fill(0, 18, 'confirmed'), ['pending', 'completed', 'cancelled', 'canceled']) as $status) {
    $db->table('appointments')->insert(['psychologist_id' => 1, 'patient_id' => 2, 'status' => $status, 'scheduled_start' => '2026-12-01 10:00:00', 'scheduled_end' => '2026-12-01 11:00:00']);
}
$db->table('appointments')->insert(['psychologist_id' => 99, 'patient_id' => 2, 'status' => 'confirmed', 'scheduled_start' => '2026-12-01 10:00:00', 'scheduled_end' => '2026-12-01 11:00:00']);
$user = App\Models\User::find(1);
$controller = new App\Http\Controllers\Psychologist\PsychologistAppointmentController;
function overview(array $query = []): array {
    global $app, $controller, $user;
    $request = Illuminate\Http\Request::create('/psychologist/appointments', 'GET', $query);
    $request->headers->set('X-Inertia', 'true');
    $app->instance('request', $request);
    $request->setUserResolver(fn () => $user);
    Illuminate\Pagination\Paginator::currentPageResolver(fn () => (int) ($query['page'] ?? 1));
    return json_decode($controller->index($request)->toResponse($request)->getContent(), true, 512, JSON_THROW_ON_ERROR)['props'];
}
function check($condition, string $message): void { if (! $condition) throw new RuntimeException($message); }
$all = overview();
check($all['summary']['total'] === 22 && $all['summary']['confirmed'] === 18, 'Summary must include all owned appointments and exclude other psychologists.');
check(count($all['appointments']['data']) === 15, 'Pagination must be preserved.');
$confirmed = overview(['status_filter' => 'confirmed', 'page' => 2]);
check($confirmed['appointments']['total'] === 18 && count($confirmed['appointments']['data']) === 3, 'Status filter must cover all pages.');
check($confirmed['summary']['total'] === 22, 'Summary must remain global while filtering.');
$cancelled = overview(['status_filter' => 'cancelled']);
check($cancelled['appointments']['total'] === 2, 'Both cancellation spellings must match.');
$none = overview(['status_filter' => 'completed', 'search_field' => 'patient', 'search_query' => 'Nobody']);
check($none['appointments']['total'] === 0, 'Name and status filters must compose.');
$invalid = overview(['status_filter' => 'invalid']);
check($invalid['appointments']['total'] === 22 && $invalid['filters']['status_filter'] === '', 'Unsupported filters must normalize safely.');
echo "Appointment summaries, ownership, pagination, combined filters, and cancellations passed.\n";
