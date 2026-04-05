<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('application')) {
            return;
        }

        Schema::table('application', function (Blueprint $table): void {
            if (! Schema::hasColumn('application', 'reviewed_by')) {
                $table->unsignedInteger('reviewed_by')->nullable()->after('updated_at');
                $table->index('reviewed_by', 'application_reviewed_by_idx');
            }

            if (! Schema::hasColumn('application', 'reviewed_at')) {
                $table->dateTime('reviewed_at')->nullable()->after('reviewed_by');
                $table->index('reviewed_at', 'application_reviewed_at_idx');
            }

            if (! Schema::hasColumn('application', 'resubmission_remarks')) {
                $table->text('resubmission_remarks')->nullable()->after('reviewed_at');
            }

            if (! Schema::hasColumn('application', 'resubmission_document_ids')) {
                $table->json('resubmission_document_ids')->nullable()->after('resubmission_remarks');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('application')) {
            return;
        }

        Schema::table('application', function (Blueprint $table): void {
            if (Schema::hasColumn('application', 'resubmission_document_ids')) {
                $table->dropColumn('resubmission_document_ids');
            }

            if (Schema::hasColumn('application', 'resubmission_remarks')) {
                $table->dropColumn('resubmission_remarks');
            }

            if (Schema::hasColumn('application', 'reviewed_at')) {
                $table->dropIndex('application_reviewed_at_idx');
                $table->dropColumn('reviewed_at');
            }

            if (Schema::hasColumn('application', 'reviewed_by')) {
                $table->dropIndex('application_reviewed_by_idx');
                $table->dropColumn('reviewed_by');
            }
        });
    }
};
