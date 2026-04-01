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
            return;
        }

        Schema::create('audit_log', function (Blueprint $table): void {
            $table->increments('log_id');
            $table->unsignedInteger('user_id');
            $table->string('module', 100);
            $table->enum('action', ['create', 'update', 'delete', 'login', 'logout', 'configure']);
            $table->text('description')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->dateTime('timestamp')->useCurrent();

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_log');
    }
};
