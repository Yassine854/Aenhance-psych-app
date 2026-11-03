<?php

namespace Tests\Feature;

use App\Http\Controllers\TurnCredentialsController;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class TurnCredentialsTest extends TestCase
{
    private function requestFor(int $id): Request
    {
        $request = Request::create('/');
        $user = new User;
        $user->id = $id;
        $request->setUserResolver(fn () => $user);

        return $request;
    }

    public function test_both_participants_receive_signed_expiring_credentials(): void
    {
        config(['turn.secret' => 'test-only-secret']);
        $appointment = new Appointment(['patient_id' => 10, 'psychologist_id' => 20, 'status' => 'confirmed']);

        foreach ([10, 20] as $id) {
            $response = (new TurnCredentialsController)($this->requestFor($id), $appointment);
            $data = $response->getData(true);
            $server = $data['iceServers'][0];
            $this->assertSame($data['expiresAt'].':'.$id, $server['username']);
            $this->assertEqualsWithDelta(now()->timestamp + 7200, $data['expiresAt'], 2);
            $this->assertSame(base64_encode(hash_hmac('sha1', $server['username'], 'test-only-secret', true)), $server['credential']);
            $this->assertSame(['turn:169.58.251.224:3478?transport=udp', 'turn:169.58.251.224:3478?transport=tcp'], $server['urls']);
            $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
            $this->assertStringNotContainsString('test-only-secret', $response->getContent());
        }
    }

    public function test_nonparticipant_is_forbidden(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionCode(0);
        (new TurnCredentialsController)($this->requestFor(30), new Appointment(['patient_id' => 10, 'psychologist_id' => 20, 'status' => 'confirmed']));
    }

    public function test_unconfirmed_appointment_is_forbidden(): void
    {
        try {
            (new TurnCredentialsController)($this->requestFor(10), new Appointment(['patient_id' => 10, 'status' => 'pending']));
            $this->fail('Expected forbidden response');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
    }

    public function test_missing_secret_returns_service_unavailable(): void
    {
        config(['turn.secret' => null]);
        $response = (new TurnCredentialsController)($this->requestFor(10), new Appointment(['patient_id' => 10, 'status' => 'confirmed']));
        $this->assertSame(503, $response->getStatusCode());
    }

    public function test_guests_cannot_fetch_credentials(): void
    {
        $this->getJson('/appointments/1/turn-credentials')->assertUnauthorized();
    }
}
