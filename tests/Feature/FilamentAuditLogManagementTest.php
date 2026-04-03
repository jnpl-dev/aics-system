<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FilamentAuditLogManagementTest extends TestCase
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

        if (! Schema::hasTable('audit_log')) {
            Schema::create('audit_log', function (Blueprint $table): void {
                $table->increments('log_id');
                $table->unsignedInteger('user_id');
                $table->string('module', 100);
                $table->enum('action', ['create', 'update', 'delete', 'login', 'logout', 'configure']);
                $table->text('description')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->dateTime('timestamp')->nullable();
            });
        }
    }

    public function test_active_user_can_access_filament_audit_logs_index(): void
    {
        $user = User::query()->create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin.audit@example.com',
            'password' => 'Strong#123',
            'role' => 'admin',
            'status' => 'active',
        ]);

        DB::table('audit_log')->insert([
            'user_id' => $user->user_id,
            'module' => 'authentication',
            'action' => 'login',
            'description' => 'event=AUTH_LOGIN_SUCCESS; email=admin.audit@example.com',
            'ip_address' => '127.0.0.1',
            'timestamp' => now(),
        ]);

        $response = $this->actingAs($user)->get('/admin/audit-logs');

        $response
            ->assertStatus(200)
            ->assertSee('Audit Logs')
            ->assertSee('authentication')
            ->assertSee('AUTH_LOGIN_SUCCESS');
    }
}
