<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\AssistanceCategory;
use App\Models\Document;
use App\Models\ApplicationLog;
use App\Models\ApplicationReview;
use App\Models\Requirement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApplicantTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_tracking_lookup_requires_reference_number_and_surname(): void
    {
        $response = $this->post(route('applicant.track.access'), []);

        $response->assertSessionHasErrors([
            'reference_code',
            'applicant_surname',
        ]);
    }

    public function test_tracking_lookup_redirects_to_tracking_page_when_credentials_match(): void
    {
        $application = $this->makeApplicationRecord(status: 'forwarded_to_mswdo', applicantLastName: 'Dela Cruz');

        $this->post(route('applicant.track.access'), [
            'reference_code' => strtolower($application->reference_code),
            'applicant_surname' => 'dela cruz',
        ])->assertRedirect(route('applicant.track.application'));

        $this->get(route('applicant.track.application'))
            ->assertSuccessful()
            ->assertSee($application->reference_code)
            ->assertSee('In Process')
            ->assertSee('Forwarded to MSWDO');
    }

    public function test_tracking_lookup_fails_for_incorrect_surname(): void
    {
        $application = $this->makeApplicationRecord(status: 'submitted', applicantLastName: 'Garcia');

        $response = $this->from(route('applicant.track'))->post(route('applicant.track.access'), [
            'reference_code' => $application->reference_code,
            'applicant_surname' => 'Santos',
        ]);

        $response
            ->assertRedirect(route('applicant.track'))
            ->assertSessionHasErrors('reference_code');
    }

    public function test_tracking_page_shows_requested_resubmission_documents_without_exposing_previous_files(): void
    {
        $category = $this->createCategory();
        $requirement = $this->createRequirement((int) $category->category_id, 'Medical Certificate');
    $application = $this->makeApplicationRecord(status: 'resubmission_required', categoryId: (int) $category->category_id);

        $requestedDocument = Document::query()->create([
            'application_id' => (int) $application->application_id,
            'requirement_id' => (int) $requirement->requirement_id,
            'uploaded_by' => null,
            'document_type' => 'supporting_document',
            'file_name' => 'old-medical.pdf',
            'file_path' => 'applications/old-medical.pdf',
            'file_size' => 1200,
            'mime_type' => 'application/pdf',
            'uploaded_at' => now(),
        ]);

        Storage::disk('supabase')->put('applications/old-medical.pdf', 'old-pdf-content');

        $application->update([
            'resubmission_document_ids' => [(int) $requestedDocument->document_id],
            'resubmission_remarks' => 'Please upload a clearer medical certificate.',
        ]);

        $this->post(route('applicant.track.access'), [
            'reference_code' => $application->reference_code,
            'applicant_surname' => $application->applicant_last_name,
        ])->assertRedirect(route('applicant.track.application'));

        $this->get(route('applicant.track.application'))
            ->assertSuccessful()
            ->assertSee('Requested Document Resubmission')
            ->assertSee('Medical Certificate')
            ->assertDontSee('old-medical.pdf');
    }

    public function test_tracking_page_hides_resubmission_uploads_when_status_is_not_resubmission_required(): void
    {
        $category = $this->createCategory();
        $requirement = $this->createRequirement((int) $category->category_id, 'Medical Certificate');
        $application = $this->makeApplicationRecord(status: 'additional_docs_required', categoryId: (int) $category->category_id);

        $requestedDocument = Document::query()->create([
            'application_id' => (int) $application->application_id,
            'requirement_id' => (int) $requirement->requirement_id,
            'uploaded_by' => null,
            'document_type' => 'supporting_document',
            'file_name' => 'old-medical.pdf',
            'file_path' => 'applications/old-medical.pdf',
            'file_size' => 1200,
            'mime_type' => 'application/pdf',
            'uploaded_at' => now(),
        ]);

        $application->update([
            'resubmission_document_ids' => [(int) $requestedDocument->document_id],
            'resubmission_remarks' => 'Please upload a clearer medical certificate.',
        ]);

        $this->post(route('applicant.track.access'), [
            'reference_code' => $application->reference_code,
            'applicant_surname' => $application->applicant_last_name,
        ])->assertRedirect(route('applicant.track.application'));

        $this->get(route('applicant.track.application'))
            ->assertSuccessful()
            ->assertDontSee('Requested Document Resubmission');
    }

    public function test_tracking_page_shows_detailed_history_from_logs_and_reviews(): void
    {
        $application = $this->makeApplicationRecord(status: 'resubmission_required', applicantLastName: 'Santos');

        if (Schema::hasTable('application_log')) {
            ApplicationLog::query()->create([
                'application_id' => (int) $application->application_id,
                'performed_by' => null,
                'action' => 'status_update',
                'from_status' => 'submitted',
                'to_status' => 'resubmission_required',
                'remarks' => 'Application returned for document resubmission.',
                'timestamp' => now()->subHours(2),
            ]);
        }

        if (Schema::hasTable('application_review')) {
            ApplicationReview::query()->create([
                'application_id' => (int) $application->application_id,
                'reviewed_by' => 1,
                'stage' => 'aics_validation',
                'decision' => 'resubmission_required',
                'feedback_remarks' => 'Validated and ready for next stage.',
                'reviewed_at' => now()->subHour(),
            ]);
        }

        $this->post(route('applicant.track.access'), [
            'reference_code' => $application->reference_code,
            'applicant_surname' => $application->applicant_last_name,
        ])->assertRedirect(route('applicant.track.application'));

        $response = $this->get(route('applicant.track.application'))
            ->assertSuccessful()
            ->assertSee('Detailed History')
            ->assertSee('Submitted')
            ->assertSee('Application submitted and to be reviewed.');

        if (Schema::hasTable('application_log')) {
            $response
                ->assertDontSee('Submitted → Resubmission Required');
        }

        if (Schema::hasTable('application_review')) {
            $response
                ->assertSee('Aics Validation (Resubmission Required)')
                ->assertSee('Validated and ready for next stage.');
        }
    }

    public function test_applicant_can_resubmit_requested_documents(): void
    {
        Storage::fake('supabase');

        $category = $this->createCategory();
        $requirement = $this->createRequirement((int) $category->category_id, 'Medical Certificate');
    $application = $this->makeApplicationRecord(status: 'resubmission_required', categoryId: (int) $category->category_id);

        $requestedDocument = Document::query()->create([
            'application_id' => (int) $application->application_id,
            'requirement_id' => (int) $requirement->requirement_id,
            'uploaded_by' => null,
            'document_type' => 'supporting_document',
            'file_name' => 'old-medical.pdf',
            'file_path' => 'applications/old-medical.pdf',
            'file_size' => 1200,
            'mime_type' => 'application/pdf',
            'uploaded_at' => now(),
        ]);

        $application->update([
            'resubmission_document_ids' => [(int) $requestedDocument->document_id],
            'resubmission_remarks' => 'Please upload a clearer medical certificate.',
        ]);

        $this->post(route('applicant.track.access'), [
            'reference_code' => $application->reference_code,
            'applicant_surname' => $application->applicant_last_name,
        ])->assertRedirect(route('applicant.track.application'));

        $response = $this->post(route('applicant.track.resubmit'), [
            'documents' => [
                'req_'.(int) $requirement->requirement_id => UploadedFile::fake()->create('medical.jpg', 100, 'image/jpeg'),
            ],
        ]);

        $response
            ->assertRedirect(route('applicant.track.application'))
            ->assertSessionHas('status');

        $application->refresh();
        $requestedDocument->refresh();

        $this->assertSame('submitted', $application->status);
        $this->assertNull($application->resubmission_remarks);
        $this->assertNull($application->resubmission_document_ids);

        $this->assertDatabaseCount('document', 1);
        $this->assertDatabaseHas('document', [
            'document_id' => (int) $requestedDocument->document_id,
            'application_id' => (int) $application->application_id,
            'requirement_id' => (int) $requirement->requirement_id,
            'document_type' => 'supporting_document',
            'mime_type' => 'application/pdf',
        ]);

        $this->assertNotSame('applications/old-medical.pdf', (string) $requestedDocument->file_path);
        Storage::disk('supabase')->assertMissing('applications/old-medical.pdf');
        Storage::disk('supabase')->assertExists((string) $requestedDocument->file_path);
    }

    private function createCategory(): AssistanceCategory
    {
        return AssistanceCategory::query()->create([
            'name' => 'Medical Assistance ' . (string) str()->uuid(),
            'description' => null,
            'is_active' => true,
            'created_at' => now(),
        ]);
    }

    private function createRequirement(int $categoryId, string $name): Requirement
    {
        return Requirement::query()->create([
            'category_id' => $categoryId,
            'name' => $name,
            'description' => null,
            'is_mandatory' => true,
            'is_active' => true,
        ]);
    }

    private function makeApplicationRecord(string $status, string $applicantLastName = 'Santos', ?int $categoryId = null): Application
    {
        $categoryId ??= (int) $this->createCategory()->category_id;

        return Application::query()->create([
            'category_id' => $categoryId,
            'submitted_by' => null,
            'reference_code' => 'AICS-20260405-123456',
            'status' => $status,
            'applicant_last_name' => $applicantLastName,
            'applicant_first_name' => 'Juan',
            'applicant_middle_name' => null,
            'applicant_sex' => 'male',
            'applicant_dob' => '1990-01-01',
            'applicant_address' => 'Sample Address',
            'applicant_phone' => '09171234567',
            'applicant_relationship_to_beneficiary' => 'Self',
            'beneficiary_last_name' => 'Santos',
            'beneficiary_first_name' => 'Juan',
            'beneficiary_middle_name' => null,
            'beneficiary_sex' => 'male',
            'beneficiary_dob' => '1990-01-01',
            'beneficiary_address' => 'Sample Address',
            'submitted_at' => now(),
            'updated_at' => now(),
            'resubmission_remarks' => null,
            'resubmission_document_ids' => null,
        ]);
    }
}
