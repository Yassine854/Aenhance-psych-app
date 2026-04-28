<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Payment;
use App\Models\PsychologistProfile;
use App\Models\User;
use App\Services\ClicToPayClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Mockery\MockInterface;
use Tests\TestCase;

class ClickToPayReturnTest extends TestCase
{
    use RefreshDatabase;

    public function test_declined_clicktopay_return_marks_payment_failed(): void
    {
        Mail::fake();

        [$patient, $psychologist, $appointment] = $this->makePendingAppointment();

        Payment::create([
            'appointment_id' => $appointment->id,
            'amount' => $appointment->price,
            'currency' => 'TND',
            'transaction_id' => 'gateway-order-declined',
            'provider' => 'clictopay',
            'status' => 'pending',
        ]);

        $this->mock(ClicToPayClient::class, function (MockInterface $mock) use ($appointment) {
            $mock->shouldReceive('isConfigured')->andReturnTrue();
            $mock->shouldReceive('getOrderStatusExtended')
                ->once()
                ->with('gateway-order-declined')
                ->andReturn([
                    'orderNumber' => 'APT-'.$appointment->id.'-20260415220000',
                    'orderStatus' => 6,
                    'actionCode' => 5,
                    'errorCode' => 0,
                    'actionCodeDescription' => 'Authorization declined',
                    'errorMessage' => 'Declined',
                ]);
        });

        $response = $this->actingAs($patient)
            ->get(route('payments.clictopay.return', [
                'appointment' => $appointment->id,
                'orderId' => 'gateway-order-declined',
            ]));

        $response->assertRedirect(route('patient.appointments'));
        $response->assertSessionHasErrors('payment');

        $appointment->refresh();
        $payment = Payment::query()->where('appointment_id', $appointment->id)->firstOrFail();

        $this->assertSame('pending', $appointment->status);
        $this->assertSame('failed', $payment->status);
        $this->assertSame('Authorization declined', $payment->failure_reason);
    }

    public function test_successful_clicktopay_return_marks_appointment_paid_and_confirmed(): void
    {
        Mail::fake();

        [$patient, $psychologist, $appointment] = $this->makePendingAppointment();

        Payment::create([
            'appointment_id' => $appointment->id,
            'amount' => $appointment->price,
            'currency' => 'TND',
            'transaction_id' => 'gateway-order-success',
            'provider' => 'clictopay',
            'status' => 'pending',
        ]);

        $this->mock(ClicToPayClient::class, function (MockInterface $mock) use ($appointment) {
            $mock->shouldReceive('isConfigured')->andReturnTrue();
            $mock->shouldReceive('getOrderStatusExtended')
                ->once()
                ->with('gateway-order-success')
                ->andReturn([
                    'orderNumber' => 'APT-'.$appointment->id.'-20260415220500',
                    'orderStatus' => 2,
                    'actionCode' => 0,
                    'errorCode' => 0,
                    'actionCodeDescription' => 'Request processed successfully',
                    'errorMessage' => 'Success',
                ]);
        });

        $response = $this->actingAs($patient)
            ->get(route('payments.clictopay.return', [
                'appointment' => $appointment->id,
                'orderId' => 'gateway-order-success',
            ]));

        $response->assertRedirect(route('patient.appointments'));
        $response->assertSessionHas('status', 'Payment successful. Appointment confirmed.');

        $appointment->refresh();
        $payment = Payment::query()->where('appointment_id', $appointment->id)->firstOrFail();

        $this->assertSame('confirmed', $appointment->status);
        $this->assertSame('paid', $payment->status);
        $this->assertNotNull($payment->paid_at);
    }

    /**
     * @return array{0:User,1:User,2:Appointment}
     */
    private function makePendingAppointment(): array
    {
        $patient = User::factory()->create([
            'role' => 'PATIENT',
        ]);

        $psychologist = User::factory()->create([
            'role' => 'PSYCHOLOGIST',
        ]);

        PsychologistProfile::create([
            'user_id' => $psychologist->id,
            'first_name' => 'Nora',
            'last_name' => 'Smith',
            'languages' => ['fr'],
            'phone' => '+21612345678',
            'date_of_birth' => '1988-04-02',
            'cv' => 'psychologists/test-cv.pdf',
            'country' => 'Tunisia',
            'city' => 'Tunis',
            'price_per_session' => 120,
            'is_approved' => true,
        ]);

        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'psychologist_id' => $psychologist->id,
            'booking_for' => 'self',
            'scheduled_start' => now()->addDays(2)->startOfHour(),
            'scheduled_end' => now()->addDays(2)->startOfHour()->addHour(),
            'status' => 'pending',
            'price' => 120,
            'currency' => 'TND',
        ]);

        return [$patient, $psychologist, $appointment];
    }
}