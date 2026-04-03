<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SupabaseAuthIntegrationTest extends TestCase
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

        config()->set('supabase.url', 'https://example.supabase.co');
        config()->set('supabase.anon_key', 'anon-test-key');
    config()->set('supabase.service_role_key', 'service-role-test-key');
        config()->set('supabase.auth_user_endpoint', '/auth/v1/user');
    }

    public function test_auth_session_requires_bearer_token(): void
    {
        $response = $this->getJson('/auth/session');

        $response->assertStatus(401);
    }

    public function test_auth_session_requires_otp_verification_before_access(): void
    {
        DB::table('user')->insert([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@example.com',
            'password' => 'hashed-password',
            'role' => 'admin',
            'status' => 'active',
            'created_at' => now(),
        ]);

        Http::fake([
            'https://example.supabase.co/auth/v1/user' => Http::response([
                'email' => 'admin@example.com',
                'id' => 'supabase-user-id',
            ], 200),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer valid-token',
        ])->getJson('/auth/session');

        $response
            ->assertStatus(403)
            ->assertJsonPath('message', 'OTP verification required before accessing protected resources.');
    }

    public function test_auth_session_returns_local_user_when_supabase_token_and_otp_are_valid(): void
    {
        DB::table('user')->insert([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@example.com',
            'password' => 'hashed-password',
            'role' => 'admin',
            'status' => 'active',
            'created_at' => now(),
        ]);

        Http::fake([
            'https://example.supabase.co/auth/v1/user' => Http::response([
                'email' => 'admin@example.com',
                'id' => 'supabase-user-id',
            ], 200),
        ]);

        Cache::put('auth:otp:verified:'.hash('sha256', 'valid-token'), [
            'email' => 'admin@example.com',
            'verified_at' => now()->toIso8601String(),
        ], now()->addHour());

        $response = $this->withHeaders([
            'Authorization' => 'Bearer valid-token',
        ])->getJson('/auth/session');

        $response
            ->assertStatus(200)
            ->assertJsonPath('user.email', 'admin@example.com')
            ->assertJsonPath('user.role', 'admin');
    }

    public function test_otp_request_and_verify_flow_succeeds(): void
    {
        DB::table('user')->insert([
            'first_name' => 'Staff',
            'last_name' => 'Member',
            'email' => 'staff@example.com',
            'password' => 'hashed-password',
            'role' => 'aics_staff',
            'status' => 'active',
            'created_at' => now(),
        ]);

        Http::fake([
            'https://example.supabase.co/auth/v1/user' => Http::response([
                'email' => 'staff@example.com',
                'id' => 'supabase-staff-id',
            ], 200),
        ]);

        Mail::fake();

        $requestOtp = $this->withHeaders([
            'Authorization' => 'Bearer otp-token',
            'Accept' => 'application/json',
        ])->postJson('/auth/otp/request');

        $requestOtp->assertStatus(200);
        $otpSessionId = (string) $requestOtp->json('otp_session_id');
        $this->assertNotSame('', $otpSessionId);

        $manualOtpSessionId = '11111111-1111-4111-8111-111111111111';

        Cache::put('auth:otp:session:'.$manualOtpSessionId, [
            'email' => 'staff@example.com',
            'user_id' => 1,
            'token_hash' => hash('sha256', 'otp-token'),
            'code_hash' => Hash::make('123456'),
            'attempts' => 0,
            'max_attempts' => 5,
        ], now()->addMinutes(10));

        $verifyOtp = $this->withHeaders([
            'Authorization' => 'Bearer otp-token',
            'Accept' => 'application/json',
        ])->postJson('/auth/otp/verify', [
            'otp_session_id' => $manualOtpSessionId,
            'otp_code' => '123456',
        ]);

        $verifyOtp
            ->assertStatus(200)
            ->assertJsonPath('message', 'OTP verified successfully. Access granted.');

        $this->assertTrue(Cache::has('auth:otp:verified:'.hash('sha256', 'otp-token')));

        $this->assertDatabaseHas('audit_log', [
            'module' => 'otp',
            'action' => 'configure',
            'user_id' => 1,
        ]);

        $this->assertGreaterThanOrEqual(
            2,
            DB::table('audit_log')
                ->where('module', 'otp')
                ->where('action', 'configure')
                ->count()
        );

        $this->assertTrue(
            DB::table('audit_log')
                ->where('description', 'like', '%event=AUTH_LOGIN_SUCCESS%')
                ->exists()
        );

        $this->assertTrue(
            DB::table('audit_log')
                ->where('description', 'like', '%event=OTP_VERIFIED%')
                ->exists()
        );
    }

    public function test_login_attempt_endpoint_records_failed_login(): void
    {
        DB::table('user')->insert([
            'first_name' => 'Staff',
            'last_name' => 'Member',
            'email' => 'staff@example.com',
            'password' => 'hashed-password',
            'role' => 'aics_staff',
            'status' => 'active',
            'created_at' => now(),
        ]);

        $response = $this->postJson('/auth/login-attempt', [
            'email' => 'staff@example.com',
            'outcome' => 'failed',
            'reason' => 'Invalid login credentials',
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonPath('message', 'Login attempt logged.');

        $this->assertTrue(
            DB::table('audit_log')
                ->where('module', 'authentication')
                ->where('action', 'login')
                ->where('description', 'like', '%event=AUTH_LOGIN_FAILED%')
                ->exists()
        );
    }

    public function test_login_attempt_lockout_after_five_failed_attempts(): void
    {
        DB::table('user')->insert([
            'first_name' => 'Staff',
            'last_name' => 'Member',
            'email' => 'staff@example.com',
            'password' => 'hashed-password',
            'role' => 'aics_staff',
            'status' => 'active',
            'created_at' => now(),
        ]);

        for ($attempt = 1; $attempt <= 4; $attempt++) {
            $response = $this->postJson('/auth/login-attempt', [
                'email' => 'staff@example.com',
                'outcome' => 'failed',
                'reason' => 'Invalid credentials',
            ]);

            $response->assertStatus(200);
        }

        $fifthAttempt = $this->postJson('/auth/login-attempt', [
            'email' => 'staff@example.com',
            'outcome' => 'failed',
            'reason' => 'Invalid credentials',
        ]);

        $fifthAttempt
            ->assertStatus(429)
            ->assertJsonPath('cooldown_active', true);

        $cooldownCheck = $this->postJson('/auth/login-cooldown-check', [
            'email' => 'staff@example.com',
        ]);

        $cooldownCheck
            ->assertStatus(429)
            ->assertJsonPath('cooldown_active', true);
    }

    public function test_audit_log_tab_renders_authentication_records(): void
    {
        DB::table('user')->insert([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@example.com',
            'password' => 'hashed-password',
            'role' => 'admin',
            'status' => 'active',
            'created_at' => now(),
        ]);

        DB::table('audit_log')->insert([
            'user_id' => 1,
            'module' => 'authentication',
            'action' => 'login',
            'description' => 'event=AUTH_LOGIN_FAILED; email=admin@example.com; reason=invalid_credentials',
            'ip_address' => '127.0.0.1',
            'timestamp' => now(),
        ]);

        Http::fake([
            'https://example.supabase.co/auth/v1/user' => Http::response([
                'email' => 'admin@example.com',
                'id' => 'supabase-admin-id',
            ], 200),
        ]);

        Cache::put('auth:otp:verified:'.hash('sha256', 'admin-token'), [
            'email' => 'admin@example.com',
            'verified_at' => now()->toIso8601String(),
        ], now()->addHour());

        $response = $this->withHeaders([
            'Authorization' => 'Bearer admin-token',
        ])->get('/dashboard/content/audit-log');

        $response
            ->assertStatus(200)
            ->assertSee('Audit logs')
            ->assertSee('authentication')
            ->assertSee('AUTH_LOGIN_FAILED');
    }

    public function test_legacy_user_management_tab_endpoint_is_retired(): void
    {
        DB::table('user')->insert([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@example.com',
            'password' => 'hashed-password',
            'role' => 'admin',
            'status' => 'active',
            'created_at' => now(),
        ]);

        Http::fake([
            'https://example.supabase.co/auth/v1/user' => Http::response([
                'email' => 'admin@example.com',
                'id' => 'supabase-admin-id',
            ], 200),
        ]);

        Cache::put('auth:otp:verified:'.hash('sha256', 'admin-token'), [
            'email' => 'admin@example.com',
            'verified_at' => now()->toIso8601String(),
        ], now()->addHour());

        $response = $this->withHeaders([
            'Authorization' => 'Bearer admin-token',
        ])->get('/dashboard/content/user-management');

        $response->assertStatus(404);
    }

    public function test_legacy_admin_users_post_endpoint_is_method_not_allowed(): void
    {
        $response = $this->post('/admin/users', [
            'first_name' => 'New',
            'last_name' => 'Staff',
            'email' => 'new.staff@example.com',
            'password' => 'Strong#123',
            'role' => 'aics_staff',
        ]);

        $response->assertStatus(405);
    }

    public function test_legacy_admin_users_post_endpoint_is_method_not_allowed_for_weak_password_payload(): void
    {
        $response = $this->post('/admin/users', [
            'first_name' => 'New',
            'last_name' => 'Staff',
            'email' => 'new.staff@example.com',
            'password' => 'weak',
            'role' => 'aics_staff',
        ]);

        $response->assertStatus(405);
    }

    public function test_admin_route_blocks_non_admin_users(): void
    {
        DB::table('user')->insert([
            'first_name' => 'Staff',
            'last_name' => 'Member',
            'email' => 'staff@example.com',
            'password' => 'hashed-password',
            'role' => 'aics_staff',
            'status' => 'active',
            'created_at' => now(),
        ]);

        Http::fake([
            'https://example.supabase.co/auth/v1/user' => Http::response([
                'email' => 'staff@example.com',
                'id' => 'supabase-staff-id',
            ], 200),
        ]);

        Cache::put('auth:otp:verified:'.hash('sha256', 'staff-token'), [
            'email' => 'staff@example.com',
            'verified_at' => now()->toIso8601String(),
        ], now()->addHour());

        $response = $this->withHeaders([
            'Authorization' => 'Bearer staff-token',
        ])->getJson('/admin/ping');

        $response->assertStatus(403);
    }
}
