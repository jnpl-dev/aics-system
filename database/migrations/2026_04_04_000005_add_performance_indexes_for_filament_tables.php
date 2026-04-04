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
        if (Schema::hasTable('audit_log')) {
            Schema::table('audit_log', function (Blueprint $table): void {
                try {
                    $table->index('timestamp', 'audit_log_timestamp_idx');
                } catch (\Throwable) {
                }

                try {
                    $table->index(['action', 'timestamp'], 'audit_log_action_timestamp_idx');
                } catch (\Throwable) {
                }

                try {
                    $table->index(['module', 'timestamp'], 'audit_log_module_timestamp_idx');
                } catch (\Throwable) {
                }
            });
        }

        if (Schema::hasTable('user')) {
            Schema::table('user', function (Blueprint $table): void {
                try {
                    $table->index('last_name', 'user_last_name_idx');
                } catch (\Throwable) {
                }

                try {
                    $table->index('role', 'user_role_idx');
                } catch (\Throwable) {
                }

                try {
                    $table->index('status', 'user_status_idx');
                } catch (\Throwable) {
                }

                try {
                    $table->index(['role', 'status'], 'user_role_status_idx');
                } catch (\Throwable) {
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('audit_log')) {
            Schema::table('audit_log', function (Blueprint $table): void {
                try {
                    $table->dropIndex('audit_log_timestamp_idx');
                } catch (\Throwable) {
                }

                try {
                    $table->dropIndex('audit_log_action_timestamp_idx');
                } catch (\Throwable) {
                }

                try {
                    $table->dropIndex('audit_log_module_timestamp_idx');
                } catch (\Throwable) {
                }
            });
        }

        if (Schema::hasTable('user')) {
            Schema::table('user', function (Blueprint $table): void {
                try {
                    $table->dropIndex('user_last_name_idx');
                } catch (\Throwable) {
                }

                try {
                    $table->dropIndex('user_role_idx');
                } catch (\Throwable) {
                }

                try {
                    $table->dropIndex('user_status_idx');
                } catch (\Throwable) {
                }

                try {
                    $table->dropIndex('user_role_status_idx');
                } catch (\Throwable) {
                }
            });
        }
    }
};
