<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ApplicantApplyValidationTest extends TestCase
{
    public function test_apply_submission_requires_mandatory_fields(): void
    {
        $response = $this->post(route('applicant.apply.store'), []);

        $response
            ->assertSessionHasErrors([
                'category_name',
                'applicant.last_name',
                'applicant.first_name',
                'applicant.middle_name',
                'applicant.sex',
                'applicant.date_of_birth',
                'applicant.phone_number',
                'applicant.address',
                'beneficiary.last_name',
                'beneficiary.first_name',
                'beneficiary.middle_name',
                'beneficiary.sex',
                'beneficiary.date_of_birth',
                'beneficiary.relationship',
                'beneficiary.address',
                'requirements.applicant_government_id',
                'requirements.applicant_cedula',
                'requirements.barangay_indigency',
            ]);
    }

    public function test_apply_submission_accepts_valid_medical_representative_payload(): void
    {
        $response = $this->post(route('applicant.apply.store'), [
            'category_name' => 'Medical Assistance',
            'applicant' => [
                'last_name' => ' Dela Cruz ',
                'first_name' => ' Juan ',
                'middle_name' => ' Santos ',
                'sex' => 'Male',
                'date_of_birth' => '1998-06-12',
                'phone_number' => '0917-123-4567',
                'address' => ' Purok 1, Barangay Sample ',
            ],
            'beneficiary' => [
                'last_name' => ' Reyes ',
                'first_name' => ' Maria ',
                'middle_name' => ' Lopez ',
                'sex' => 'Female',
                'date_of_birth' => '1990-04-18',
                'relationship' => 'Representative',
                'address' => ' Sitio Proper, Barangay Sample ',
            ],
            'requirements' => [
                'medical_certificate' => UploadedFile::fake()->create('medical-certificate.pdf', 100, 'application/pdf'),
                'prescription' => UploadedFile::fake()->create('prescription.pdf', 100, 'application/pdf'),
                'applicant_government_id' => UploadedFile::fake()->create('applicant-id.pdf', 100, 'application/pdf'),
                'beneficiary_government_id' => UploadedFile::fake()->create('beneficiary-id.pdf', 100, 'application/pdf'),
                'applicant_cedula' => UploadedFile::fake()->create('cedula.pdf', 100, 'application/pdf'),
                'barangay_indigency' => UploadedFile::fake()->create('indigency.pdf', 100, 'application/pdf'),
                'authorization_letter' => UploadedFile::fake()->create('authorization.pdf', 100, 'application/pdf'),
            ],
        ]);

        $response
            ->assertRedirect(route('applicant.apply'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status');
    }
}
