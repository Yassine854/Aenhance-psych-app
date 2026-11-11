<?php

namespace Tests\Unit;

use App\Models\PatientProfile;
use App\Models\PsychologistProfile;
use App\Models\User;
use PHPUnit\Framework\TestCase;

class VideoCallNameTest extends TestCase
{
    public function test_patient_uses_profile_first_name_instead_of_account_name(): void
    {
        $user = new User(['name' => 'Different Account Name', 'role' => 'PATIENT']);
        $user->setRelation('patientProfile', new PatientProfile(['first_name' => ' Amine ']));

        $this->assertSame('Amine', $user->videoCallFirstName());
    }

    public function test_psychologist_preserves_a_compound_first_name(): void
    {
        $user = new User(['name' => 'Other Name', 'role' => 'PSYCHOLOGIST']);
        $user->setRelation('psychologistProfile', new PsychologistProfile(['first_name' => 'Mary Jane']));

        $this->assertSame('Mary Jane', $user->videoCallFirstName());
    }

    public function test_missing_or_empty_profile_first_names_fall_back_to_account_first_name(): void
    {
        $user = new User(['name' => ' Amine   Ben Ali ', 'role' => 'PATIENT']);
        $user->setRelation('patientProfile', null);
        $this->assertSame('Amine', $user->videoCallFirstName());

        $user->setRelation('patientProfile', new PatientProfile(['first_name' => ' ']));
        $this->assertSame('Amine', $user->videoCallFirstName());
    }
}
