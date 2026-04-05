<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('assistance_category')) {
            Schema::create('assistance_category', function (Blueprint $table): void {
                $table->increments('category_id');
                $table->string('name', 255)->unique();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->dateTime('created_at')->useCurrent();
            });
        }

        if (! Schema::hasTable('requirement')) {
            Schema::create('requirement', function (Blueprint $table): void {
                $table->increments('requirement_id');
                $table->unsignedInteger('category_id');
                $table->string('name', 255);
                $table->text('description')->nullable();
                $table->boolean('is_mandatory')->default(true);
                $table->boolean('is_active')->default(true);

                $table->foreign('category_id')->references('category_id')->on('assistance_category')->cascadeOnDelete();
                $table->index(['category_id', 'name'], 'requirement_category_name_idx');
            });
        }

        if (! Schema::hasTable('application')) {
            Schema::create('application', function (Blueprint $table): void {
                $table->increments('application_id');
                $table->unsignedInteger('category_id');
                $table->unsignedInteger('submitted_by')->nullable();
                $table->string('reference_code', 50)->unique();
                $table->enum('status', [
                    'submitted',
                    'under_review',
                    'resubmission_required',
                    'forwarded_to_mswd',
                    'pending_additional_docs',
                    'approved_by_mswd',
                    'coding',
                    'forwarded_to_mayor',
                    'approved_by_mayor',
                    'voucher_preparation',
                    'forwarded_to_accounting',
                    'forwarded_to_treasury',
                    'on_hold',
                    'cheque_ready',
                    'claimed',
                ])->default('submitted');
                $table->string('applicant_last_name', 100);
                $table->string('applicant_first_name', 100);
                $table->string('applicant_middle_name', 100)->nullable();
                $table->enum('applicant_sex', ['male', 'female']);
                $table->date('applicant_dob');
                $table->string('applicant_address', 500);
                $table->string('applicant_phone', 20);
                $table->string('applicant_relationship_to_beneficiary', 100);
                $table->string('beneficiary_last_name', 100);
                $table->string('beneficiary_first_name', 100);
                $table->string('beneficiary_middle_name', 100)->nullable();
                $table->enum('beneficiary_sex', ['male', 'female']);
                $table->date('beneficiary_dob');
                $table->string('beneficiary_address', 500);
                $table->dateTime('submitted_at')->useCurrent();
                $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

                $table->foreign('category_id')->references('category_id')->on('assistance_category')->restrictOnDelete();
                $table->index(['status', 'submitted_at'], 'application_status_submitted_idx');
            });
        }

        if (! Schema::hasTable('document')) {
            Schema::create('document', function (Blueprint $table): void {
                $table->increments('document_id');
                $table->unsignedInteger('application_id');
                $table->unsignedInteger('requirement_id')->nullable();
                $table->unsignedInteger('uploaded_by')->nullable();
                $table->enum('document_type', ['supporting_document', 'authorization_letter', 'allotment_slip', 'other']);
                $table->string('file_name', 255);
                $table->string('file_path', 500);
                $table->unsignedBigInteger('file_size');
                $table->string('mime_type', 100);
                $table->dateTime('uploaded_at')->useCurrent();

                $table->foreign('application_id')->references('application_id')->on('application')->cascadeOnDelete();
                $table->foreign('requirement_id')->references('requirement_id')->on('requirement')->nullOnDelete();
                $table->index(['application_id', 'document_type'], 'document_app_type_idx');
            });
        }

        $this->seedAssistanceCategoriesAndRequirements();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document');
        Schema::dropIfExists('application');
        Schema::dropIfExists('requirement');
        Schema::dropIfExists('assistance_category');
    }

    private function seedAssistanceCategoriesAndRequirements(): void
    {
        $categories = [
            ['name' => 'Medical Assistance', 'description' => null],
            ['name' => 'Hospital Assistance', 'description' => null],
            ['name' => 'Burial Assistance', 'description' => null],
        ];

        foreach ($categories as $category) {
            $exists = DB::table('assistance_category')->where('name', $category['name'])->exists();

            if (! $exists) {
                DB::table('assistance_category')->insert([
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'is_active' => true,
                    'created_at' => now(),
                ]);
            }
        }

        $categoryIds = DB::table('assistance_category')
            ->whereIn('name', ['Medical Assistance', 'Hospital Assistance', 'Burial Assistance'])
            ->pluck('category_id', 'name');

        $requirements = [
            'Medical Assistance' => [
                ['name' => 'Medical Certificate', 'is_mandatory' => true],
                ['name' => 'Prescription', 'is_mandatory' => true],
                ['name' => "Applicant's Government ID", 'is_mandatory' => true],
                ['name' => "Beneficiary's Government ID", 'is_mandatory' => true],
                ['name' => "Applicant's Cedula", 'is_mandatory' => true],
                ['name' => 'Barangay Indigency', 'is_mandatory' => true],
                ['name' => 'Authorization Letter', 'is_mandatory' => false],
            ],
            'Hospital Assistance' => [
                ['name' => 'Hospital Bill', 'is_mandatory' => true],
                ['name' => 'Prescription', 'is_mandatory' => true],
                ['name' => 'Medical Certificate/Abstract', 'is_mandatory' => true],
                ['name' => "Applicant's Government ID", 'is_mandatory' => true],
                ['name' => "Beneficiary's Government ID", 'is_mandatory' => true],
                ['name' => "Applicant's Cedula", 'is_mandatory' => true],
                ['name' => 'Barangay Indigency', 'is_mandatory' => true],
                ['name' => 'Authorization Letter', 'is_mandatory' => false],
            ],
            'Burial Assistance' => [
                ['name' => 'Certified Copy of Birth Certificate', 'is_mandatory' => true],
                ['name' => "Applicant's Government ID", 'is_mandatory' => true],
                ['name' => "Applicant's Cedula", 'is_mandatory' => true],
                ['name' => "Beneficiary's Barangay Residency", 'is_mandatory' => true],
                ['name' => 'Barangay Indigency', 'is_mandatory' => true],
                ['name' => 'Authorization Letter', 'is_mandatory' => false],
            ],
        ];

        foreach ($requirements as $categoryName => $items) {
            $categoryId = $categoryIds[$categoryName] ?? null;

            if (! $categoryId) {
                continue;
            }

            foreach ($items as $item) {
                $exists = DB::table('requirement')
                    ->where('category_id', $categoryId)
                    ->where('name', $item['name'])
                    ->exists();

                if (! $exists) {
                    DB::table('requirement')->insert([
                        'category_id' => $categoryId,
                        'name' => $item['name'],
                        'description' => null,
                        'is_mandatory' => $item['is_mandatory'],
                        'is_active' => true,
                    ]);
                }
            }
        }
    }
};
