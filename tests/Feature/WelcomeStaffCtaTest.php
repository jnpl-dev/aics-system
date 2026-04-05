<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class WelcomeStaffCtaTest extends TestCase
{
    public function test_guest_sees_staff_login_call_to_action(): void
    {
        $response = $this->get('/');

        $response
            ->assertSuccessful()
            ->assertSee('Staff Login')
            ->assertSee('href="'.route('staff.login').'"', false);
    }

    public function test_authenticated_admin_user_sees_return_to_dashboard_call_to_action(): void
    {
        $this->be(new User([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@example.com',
            'role' => 'admin',
            'status' => 'active',
        ]));

        $response = $this->get('/');

        $response
            ->assertSuccessful()
            ->assertSee('Return to Dashboard')
            ->assertSee('href="'.url('/admin').'"', false);
    }

    public function test_authenticated_aics_staff_user_sees_return_to_dashboard_call_to_action(): void
    {
        $this->be(new User([
            'first_name' => 'Aics',
            'last_name' => 'Staff',
            'email' => 'staff@example.com',
            'role' => 'aics_staff',
            'status' => 'active',
        ]));

        $response = $this->get('/');

        $response
            ->assertSuccessful()
            ->assertSee('Return to Dashboard')
            ->assertSee('href="'.url('/aics-staff').'"', false);
    }
}
