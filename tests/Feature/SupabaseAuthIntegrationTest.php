<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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

    public function test_auth_session_returns_local_user_when_supabase_token_is_valid(): void
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
            ->assertStatus(200)
            ->assertJsonPath('user.email', 'admin@example.com')
            ->assertJsonPath('user.role', 'admin');
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

        $response = $this->withHeaders([
            'Authorization' => 'Bearer staff-token',
        ])->getJson('/admin/ping');

        $response->assertStatus(403);
    }
}
