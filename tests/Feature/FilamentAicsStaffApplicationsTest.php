<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FilamentAicsStaffApplicationsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('user')) {
            Schema::create('user', function (Blueprint $table): void {
                $table->increments('user_id');
                $table->string('first_name');
                $table->string('last_name');
                $table->string('email')->unique();
                $table->string('password');
                $table->string('role');
                $table->string('status')->default('active');
                $table->dateTime('created_at')->nullable();
            });
        }

        if (! Schema::hasTable('application')) {
            Schema::create('application', function (Blueprint $table): void {
                $table->increments('application_id');
                $table->unsignedInteger('category_id')->nullable();
                $table->unsignedInteger('submitted_by')->nullable();
                $table->string('reference_code', 50)->unique();
                $table->string('status', 100)->default('submitted');
                $table->string('applicant_last_name', 100);
                $table->string('applicant_first_name', 100);
                $table->string('applicant_middle_name', 100)->nullable();
                $table->string('applicant_sex', 20)->nullable();
                $table->date('applicant_dob')->nullable();
                $table->string('applicant_address', 500)->nullable();
                $table->string('applicant_phone', 20)->nullable();
                $table->string('applicant_relationship_to_beneficiary', 100)->nullable();
                $table->string('beneficiary_last_name', 100)->nullable();
                $table->string('beneficiary_first_name', 100)->nullable();
                $table->string('beneficiary_middle_name', 100)->nullable();
                $table->string('beneficiary_sex', 20)->nullable();
                $table->date('beneficiary_dob')->nullable();
                $table->string('beneficiary_address', 500)->nullable();
                $table->dateTime('submitted_at')->nullable();
                $table->dateTime('updated_at')->nullable();
                $table->unsignedInteger('reviewed_by')->nullable();
                $table->dateTime('reviewed_at')->nullable();
                $table->text('resubmission_remarks')->nullable();
                $table->text('resubmission_document_ids')->nullable();
            });
        }

        if (Schema::hasTable('assistance_category')) {
            Schema::disableForeignKeyConstraints();
            DB::table('assistance_category')->updateOrInsert(
                ['category_id' => 1],
                [
                    'name' => 'Medical Assistance',
                    'description' => 'Test category',
                    'is_active' => true,
                    'created_at' => now(),
                ]
            );
            Schema::enableForeignKeyConstraints();
        }

        if (! Schema::hasTable('document')) {
            Schema::create('document', function (Blueprint $table): void {
                $table->increments('document_id');
                $table->unsignedInteger('application_id');
                $table->unsignedInteger('requirement_id')->nullable();
                $table->unsignedInteger('uploaded_by')->nullable();
                $table->string('document_type', 100)->default('supporting_document');
                $table->string('file_name', 255);
                $table->string('file_path', 500);
                $table->unsignedBigInteger('file_size')->default(1);
                $table->string('mime_type', 100)->default('application/pdf');
                $table->dateTime('uploaded_at')->nullable();
            });
        }
    }

    public function test_active_aics_staff_can_access_filament_applications_index(): void
    {
        $this->seedTestApplication($this->uniqueReference('REF-PENDING'), 'submitted');
        $this->seedTestApplication($this->uniqueReference('REF-FORWARDED'), 'forwarded_to_mswdo');

        $staff = User::query()->create([
            'first_name' => 'Aics',
            'last_name' => 'Staff',
            'email' => $this->uniqueEmail('aics.staff'),
            'password' => 'Strong#123',
            'role' => 'aics_staff',
            'status' => 'active',
        ]);

        $response = $this->actingAs($staff)->get('/aics-staff/applications');

        $response
            ->assertStatus(200)
            ->assertSee('Applications')
            ->assertSee('Pending')
            ->assertSee('Forwarded')
            ->assertSee('Returned')
            ->assertSee('Review');
    }

    public function test_active_aics_staff_can_open_review_page_for_pending_application(): void
    {
        $referenceCode = $this->uniqueReference('REF-REVIEW');
        $applicationId = $this->seedTestApplication($referenceCode, 'submitted');
        $this->seedTestDocument($applicationId, 'review_document.pdf');

        $staff = User::query()->create([
            'first_name' => 'Aics',
            'last_name' => 'Reviewer',
            'email' => $this->uniqueEmail('aics.staff.reviewer'),
            'password' => 'Strong#123',
            'role' => 'aics_staff',
            'status' => 'active',
        ]);

        $response = $this->actingAs($staff)->get("/aics-staff/applications/{$applicationId}/review");

        $response
            ->assertStatus(200)
            ->assertSee('Review Application: ' . $referenceCode)
            ->assertSee('Review Trail')
            ->assertSee('View PDF')
            ->assertSeeText('Return & Request Resubmission')
            ->assertSee('Forward to MSWDO');
    }

    public function test_active_aics_staff_can_open_view_page_for_forwarded_application(): void
    {
        $referenceCode = $this->uniqueReference('REF-VIEW');
        $applicationId = $this->seedTestApplication($referenceCode, 'forwarded_to_mswdo');
        $this->seedTestDocument($applicationId, 'forwarded_document.pdf');

        $staff = User::query()->create([
            'first_name' => 'Aics',
            'last_name' => 'Viewer',
            'email' => $this->uniqueEmail('aics.staff.viewer'),
            'password' => 'Strong#123',
            'role' => 'aics_staff',
            'status' => 'active',
        ]);

        $response = $this->actingAs($staff)->get("/aics-staff/applications/{$applicationId}/view");

        $response
            ->assertStatus(200)
            ->assertSee('View Application: ' . $referenceCode)
            ->assertSee('Application Profile (View Only)')
            ->assertSee('Review Trail')
            ->assertSee('View PDF');
    }

    public function test_active_aics_staff_with_title_case_status_can_access_panel(): void
    {
        $staff = User::query()->create([
            'first_name' => 'Aics',
            'last_name' => 'Staff',
            'email' => $this->uniqueEmail('aics.staff.hyphen'),
            'password' => 'Strong#123',
            'role' => 'aics_staff',
            'status' => 'Active',
        ]);

        $response = $this->actingAs($staff)->get('/aics-staff/applications');

        $response->assertStatus(200);
    }

    public function test_active_aics_staff_cannot_access_admin_user_management_index(): void
    {
        $staff = User::query()->create([
            'first_name' => 'Aics',
            'last_name' => 'Staff',
            'email' => $this->uniqueEmail('aics.staff.noadmin'),
            'password' => 'Strong#123',
            'role' => 'aics_staff',
            'status' => 'active',
        ]);

        $response = $this->actingAs($staff)->get('/admin/users');

        $response->assertStatus(403);
    }

    public function test_active_aics_staff_can_access_analytics_page(): void
    {
        $this->seedTestApplication($this->uniqueReference('REF-ANALYTICS-A'), 'submitted');
        $this->seedTestApplication($this->uniqueReference('REF-ANALYTICS-B'), 'resubmission_required');

        $staff = User::query()->create([
            'first_name' => 'Aics',
            'last_name' => 'Analyst',
            'email' => $this->uniqueEmail('aics.staff.analytics'),
            'password' => 'Strong#123',
            'role' => 'aics_staff',
            'status' => 'active',
        ]);

        $response = $this->actingAs($staff)->get('/aics-staff/analytics');

        $response
            ->assertStatus(200)
            ->assertSee('AICS Staff Analytics')
            ->assertSee('Applications')
            ->assertSee('Assistance Code')
            ->assertSee('Submitted')
            ->assertSee('Forwarded')
            ->assertSee('Returned')
            ->assertSee('New Applications List')
            ->assertSee('Old Applications List')
            ->assertSee('Application Trend')
            ->assertSee('Week')
            ->assertSee('Month')
            ->assertSee('Year');
    }

    public function test_aics_staff_dashboard_includes_view_full_analytics_entrypoint(): void
    {
        $staff = User::query()->create([
            'first_name' => 'Aics',
            'last_name' => 'Dashboard',
            'email' => $this->uniqueEmail('aics.staff.dashboard'),
            'password' => 'Strong#123',
            'role' => 'aics_staff',
            'status' => 'active',
        ]);

        $response = $this->actingAs($staff)->get('/aics-staff');

        $response
            ->assertStatus(200)
            ->assertSee('AICS Staff Dashboard')
            ->assertSee('View Full Analytics')
            ->assertSee('href="' . url('/aics-staff/analytics') . '"', false);
    }

    private function seedTestApplication(string $referenceCode, string $status): int
    {
        return (int) DB::table('application')->insertGetId([
            'category_id' => 1,
            'submitted_by' => null,
            'reference_code' => $referenceCode,
            'status' => $status,
            'applicant_last_name' => 'Applicant',
            'applicant_first_name' => 'Test',
            'applicant_middle_name' => null,
            'applicant_sex' => 'male',
            'applicant_dob' => '1990-01-01',
            'applicant_address' => 'Test Applicant Address',
            'applicant_phone' => '09123456789',
            'applicant_relationship_to_beneficiary' => 'Self',
            'beneficiary_last_name' => 'Beneficiary',
            'beneficiary_first_name' => 'Test',
            'beneficiary_middle_name' => null,
            'beneficiary_sex' => 'female',
            'beneficiary_dob' => '1992-02-02',
            'beneficiary_address' => 'Test Beneficiary Address',
            'submitted_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function seedTestDocument(int $applicationId, string $fileName): void
    {
        DB::table('document')->insert([
            'application_id' => $applicationId,
            'document_type' => 'supporting_document',
            'file_name' => $fileName,
            'file_path' => 'applications/test/' . $fileName,
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
            'uploaded_at' => now(),
        ]);
    }

    private function uniqueEmail(string $prefix): string
    {
        return $prefix . '+' . Str::lower(Str::random(8)) . '@example.com';
    }

    private function uniqueReference(string $prefix): string
    {
        return $prefix . '-' . Str::upper(Str::random(6));
    }
}
