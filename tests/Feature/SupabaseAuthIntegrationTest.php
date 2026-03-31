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

        config()->set('supabase.url', 'https://example.supabase.co');
        config()->set('supabase.anon_key', 'anon-test-key');
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
