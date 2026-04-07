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
        if (! Schema::hasTable('exports')) {
            return;
        }

        if (! Schema::hasColumn('exports', 'user_user_id')) {
            Schema::table('exports', function (Blueprint $table): void {
                $table->unsignedInteger('user_user_id')->nullable()->index();
            });
        }

        if (Schema::hasColumn('exports', 'user_id')) {
            DB::table('exports')
                ->whereNull('user_user_id')
                ->update([
                    'user_user_id' => DB::raw('user_id'),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('exports')) {
            return;
        }

        if (Schema::hasColumn('exports', 'user_user_id')) {
            Schema::table('exports', function (Blueprint $table): void {
                $table->dropIndex(['user_user_id']);
                $table->dropColumn('user_user_id');
            });
        }
    }
};
