<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('application')) {
            Schema::table('application', function (Blueprint $table): void {
                try {
                    $table->index('status', 'application_status_idx');
                } catch (Throwable) {
                }

                try {
                    $table->index('reference_code', 'application_reference_code_idx');
                } catch (Throwable) {
                }

                try {
                    $table->index('submitted_at', 'application_submitted_at_idx');
                } catch (Throwable) {
                }

                try {
                    $table->index(['applicant_last_name', 'applicant_first_name'], 'application_applicant_name_idx');
                } catch (Throwable) {
                }

                try {
                    $table->index(['beneficiary_last_name', 'beneficiary_first_name'], 'application_beneficiary_name_idx');
                } catch (Throwable) {
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('application')) {
            Schema::table('application', function (Blueprint $table): void {
                try {
                    $table->dropIndex('application_status_idx');
                } catch (Throwable) {
                }

                try {
                    $table->dropIndex('application_reference_code_idx');
                } catch (Throwable) {
                }

                try {
                    $table->dropIndex('application_submitted_at_idx');
                } catch (Throwable) {
                }

                try {
                    $table->dropIndex('application_applicant_name_idx');
                } catch (Throwable) {
                }

                try {
                    $table->dropIndex('application_beneficiary_name_idx');
                } catch (Throwable) {
                }
            });
        }
    }
};
