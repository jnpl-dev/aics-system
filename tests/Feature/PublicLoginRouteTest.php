<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicLoginRouteTest extends TestCase
{
    public function test_staff_login_entry_redirects_to_public_login(): void
    {
        $this->get('/staff-login')
            ->assertRedirect('/login');
    }

    public function test_panel_login_entries_redirect_to_public_login(): void
    {
        $this->get('/admin/login')
            ->assertRedirect('/login');

        $this->get('/aics-staff/login')
            ->assertRedirect('/login');
    }

    public function test_public_login_page_is_reachable_for_guests(): void
    {
        $this->get('/login')
            ->assertSuccessful();
    }
}
