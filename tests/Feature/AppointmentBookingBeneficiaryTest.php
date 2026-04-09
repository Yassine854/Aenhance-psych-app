<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\AppointmentBeneficiary;
use App\Models\PsychologistProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentBookingBeneficiaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_can_book_appointment_for_self(): void
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
            'date_of_birth' => '1988-04-02',
            'price_per_session' => 120,
            'is_approved' => true,
        ]);

        $scheduledStart = now()->addDays(2)->startOfHour()->toIso8601String();

        $response = $this->actingAs($patient)->post(route('appointments.store'), [
            'psychologist_id' => $psychologist->id,
            'scheduled_start' => $scheduledStart,
            'booking_for' => 'self',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $appointment = Appointment::query()->first();

        $this->assertNotNull($appointment);
        $this->assertSame('self', $appointment->booking_for);
        $this->assertSame($patient->id, $appointment->patient_id);
        $this->assertDatabaseCount('appointment_beneficiaries', 0);
    }

    public function test_patient_can_book_appointment_for_another_person(): void
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
            'date_of_birth' => '1988-04-02',
            'price_per_session' => 120,
            'is_approved' => true,
        ]);

        $scheduledStart = now()->addDays(2)->startOfHour()->toIso8601String();

        $response = $this->actingAs($patient)->post(route('appointments.store'), [
            'psychologist_id' => $psychologist->id,
            'scheduled_start' => $scheduledStart,
            'booking_for' => 'other',
            'beneficiary_first_name' => 'Lina',
            'beneficiary_last_name' => 'Doe',
            'beneficiary_date_of_birth' => '2018-06-15',
            'beneficiary_gender' => 'female',
            'beneficiary_relationship' => 'Daughter',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $appointment = Appointment::query()->with('beneficiary')->first();

        $this->assertNotNull($appointment);
        $this->assertSame('other', $appointment->booking_for);
        $this->assertNotNull($appointment->beneficiary);
        $this->assertSame('Lina', $appointment->beneficiary->first_name);
        $this->assertSame('Doe', $appointment->beneficiary->last_name);
        $this->assertSame('Daughter', $appointment->beneficiary->relationship_to_patient);
    }

    public function test_booking_for_another_person_requires_beneficiary_details(): void
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
            'date_of_birth' => '1988-04-02',
            'price_per_session' => 120,
            'is_approved' => true,
        ]);

        $scheduledStart = now()->addDays(2)->startOfHour()->toIso8601String();

        $response = $this->from('/appointments/book/'.$psychologist->id)->actingAs($patient)->post(route('appointments.store'), [
            'psychologist_id' => $psychologist->id,
            'scheduled_start' => $scheduledStart,
            'booking_for' => 'other',
        ]);

        $response->assertRedirect('/appointments/book/'.$psychologist->id);
        $response->assertSessionHasErrors([
            'beneficiary_first_name',
            'beneficiary_last_name',
            'beneficiary_date_of_birth',
            'beneficiary_relationship',
        ]);
        $this->assertDatabaseCount('appointments', 0);
    }

    public function test_booking_for_another_person_requires_beneficiary_to_be_at_least_one_year_old(): void
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
            'date_of_birth' => '1988-04-02',
            'price_per_session' => 120,
            'is_approved' => true,
        ]);

        $scheduledStart = now()->addDays(2)->startOfHour()->toIso8601String();

        $response = $this->from('/appointments/book/'.$psychologist->id)->actingAs($patient)->post(route('appointments.store'), [
            'psychologist_id' => $psychologist->id,
            'scheduled_start' => $scheduledStart,
            'booking_for' => 'other',
            'beneficiary_first_name' => 'Baby',
            'beneficiary_last_name' => 'Doe',
            'beneficiary_date_of_birth' => now()->subMonths(6)->toDateString(),
            'beneficiary_gender' => 'female',
            'beneficiary_relationship' => 'Daughter',
        ]);

        $response->assertRedirect('/appointments/book/'.$psychologist->id);
        $response->assertSessionHasErrors([
            'beneficiary_date_of_birth',
        ]);
        $this->assertDatabaseCount('appointments', 0);
    }

    public function test_booking_page_includes_unique_previous_beneficiaries_for_autofill(): void
    {
        $patient = User::factory()->create([
            'role' => 'PATIENT',
        ]);

        $otherPatient = User::factory()->create([
            'role' => 'PATIENT',
        ]);

        $psychologist = User::factory()->create([
            'role' => 'PSYCHOLOGIST',
        ]);

        $profile = PsychologistProfile::create([
            'user_id' => $psychologist->id,
            'first_name' => 'Nora',
            'last_name' => 'Smith',
            'date_of_birth' => '1988-04-02',
            'price_per_session' => 120,
            'is_approved' => true,
        ]);

        $firstAppointment = Appointment::create([
            'patient_id' => $patient->id,
            'psychologist_id' => $psychologist->id,
            'booking_for' => 'other',
            'scheduled_start' => now()->addDays(3),
            'scheduled_end' => now()->addDays(3)->addHour(),
            'status' => 'pending',
            'price' => 120,
            'currency' => 'TND',
        ]);

        AppointmentBeneficiary::create([
            'appointment_id' => $firstAppointment->id,
            'first_name' => 'Lina',
            'last_name' => 'Doe',
            'date_of_birth' => '2018-06-15',
            'gender' => 'female',
            'relationship_to_patient' => 'Daughter',
        ]);

        $duplicateAppointment = Appointment::create([
            'patient_id' => $patient->id,
            'psychologist_id' => $psychologist->id,
            'booking_for' => 'other',
            'scheduled_start' => now()->addDays(5),
            'scheduled_end' => now()->addDays(5)->addHour(),
            'status' => 'pending',
            'price' => 120,
            'currency' => 'TND',
        ]);

        AppointmentBeneficiary::create([
            'appointment_id' => $duplicateAppointment->id,
            'first_name' => 'Lina',
            'last_name' => 'Doe',
            'date_of_birth' => '2018-06-15',
            'gender' => 'female',
            'relationship_to_patient' => 'Daughter',
        ]);

        $differentAppointment = Appointment::create([
            'patient_id' => $patient->id,
            'psychologist_id' => $psychologist->id,
            'booking_for' => 'other',
            'scheduled_start' => now()->addDays(7),
            'scheduled_end' => now()->addDays(7)->addHour(),
            'status' => 'pending',
            'price' => 120,
            'currency' => 'TND',
        ]);

        AppointmentBeneficiary::create([
            'appointment_id' => $differentAppointment->id,
            'first_name' => 'Adam',
            'last_name' => 'Doe',
            'date_of_birth' => '2016-01-05',
            'gender' => 'male',
            'relationship_to_patient' => 'Son',
        ]);

        $otherPatientAppointment = Appointment::create([
            'patient_id' => $otherPatient->id,
            'psychologist_id' => $psychologist->id,
            'booking_for' => 'other',
            'scheduled_start' => now()->addDays(9),
            'scheduled_end' => now()->addDays(9)->addHour(),
            'status' => 'pending',
            'price' => 120,
            'currency' => 'TND',
        ]);

        AppointmentBeneficiary::create([
            'appointment_id' => $otherPatientAppointment->id,
            'first_name' => 'Sara',
            'last_name' => 'Doe',
            'date_of_birth' => '2015-02-20',
            'gender' => 'female',
            'relationship_to_patient' => 'Niece',
        ]);

        $response = $this->actingAs($patient)->get(route('appointments.book', $profile));

        $response->assertOk();

        $page = $response->viewData('page');
        $this->assertSame('Patient/Appointments/Book', $page['component']);
        $this->assertCount(2, $page['props']['previousBeneficiaries']);
        $this->assertSame('Adam Doe', $page['props']['previousBeneficiaries'][0]['full_name']);
        $this->assertSame('Lina Doe', $page['props']['previousBeneficiaries'][1]['full_name']);
    }
}