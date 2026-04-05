<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApplicantApplyValidationTest extends TestCase
{
    use RefreshDatabase;

    private function fakeImageUpload(string $name): UploadedFile
    {
        return UploadedFile::fake()->create($name, 100, 'image/jpeg');
    }

    public function test_apply_submission_requires_mandatory_fields(): void
    {
        $response = $this->post(route('applicant.apply.store'), []);

        $response
            ->assertSessionHasErrors([
                'category_name',
                'applicant.last_name',
                'applicant.first_name',
                'applicant.sex',
                'applicant.date_of_birth',
                'applicant.phone_number',
                'applicant.address',
                'beneficiary.last_name',
                'beneficiary.first_name',
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
        Storage::fake('supabase');

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
                'medical_certificate' => $this->fakeImageUpload('medical-certificate.jpg'),
                'prescription' => $this->fakeImageUpload('prescription.jpg'),
                'applicant_government_id' => $this->fakeImageUpload('applicant-id.jpg'),
                'beneficiary_government_id' => $this->fakeImageUpload('beneficiary-id.jpg'),
                'applicant_cedula' => $this->fakeImageUpload('cedula.jpg'),
                'barangay_indigency' => $this->fakeImageUpload('indigency.jpg'),
                'authorization_letter' => $this->fakeImageUpload('authorization.jpg'),
            ],
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertSessionMissing('status');

        $this->assertDatabaseCount('application', 1);
        $this->assertDatabaseCount('document', 7);

        $application = \App\Models\Application::query()->firstOrFail();

        $this->assertSame('submitted', $application->status);
        $this->assertNotEmpty($application->reference_code);
        $this->assertSame('09171234567', $application->applicant_phone);

    $response->assertRedirect(route('applicant.apply.success', ['referenceCode' => $application->reference_code]));

        $storedDocuments = \App\Models\Document::query()->where('application_id', $application->application_id)->get();

        foreach ($storedDocuments as $document) {
            $this->assertNotEmpty($document->file_path);
            $this->assertStringEndsWith('.pdf', strtolower($document->file_path));
            $this->assertSame('application/pdf', $document->mime_type);
            $this->assertStringEndsWith('.pdf', strtolower($document->file_name));
            Storage::disk('supabase')->assertExists($document->file_path);
        }
    }
}
