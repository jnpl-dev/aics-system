<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FilamentUserManagementTest extends TestCase
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
    }

    public function test_active_user_can_access_filament_user_management_index(): void
    {
        $user = User::query()->create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin+' . uniqid() . '@example.com',
            'password' => 'Strong#123',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get('/admin/users');

        $response
            ->assertStatus(200)
            ->assertSee('User Management');
    }

    public function test_active_user_can_access_filament_create_user_page(): void
    {
        $user = User::query()->create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin.create+' . uniqid() . '@example.com',
            'password' => 'Strong#123',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get('/admin/users/create');

        $response
            ->assertStatus(200)
            ->assertSee('First Name')
            ->assertSee('Last Name')
            ->assertSee('Email');
    }
}