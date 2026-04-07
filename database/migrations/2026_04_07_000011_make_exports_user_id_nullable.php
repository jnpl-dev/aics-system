<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('exports') || ! Schema::hasColumn('exports', 'user_id')) {
            return;
        }

        DB::statement('ALTER TABLE `exports` MODIFY `user_id` INT UNSIGNED NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('exports') || ! Schema::hasColumn('exports', 'user_id')) {
            return;
        }

        DB::statement('UPDATE `exports` SET `user_id` = 0 WHERE `user_id` IS NULL');
        DB::statement('ALTER TABLE `exports` MODIFY `user_id` INT UNSIGNED NOT NULL');
    }
};
