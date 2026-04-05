<?php

namespace Tests\Unit;

use App\Models\User;
use Filament\Panel;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserModelMappingTest extends TestCase
{
    public function test_user_model_uses_legacy_user_table_and_primary_key(): void
    {
        $model = new User();

        $this->assertSame('user', $model->getTable());
        $this->assertSame('user_id', $model->getKeyName());
    }

    public function test_user_model_hashes_plain_password_values(): void
    {
        $model = new User();
        $model->password = 'plain-secret';

        $this->assertNotSame('plain-secret', $model->password);
        $this->assertTrue(Hash::check('plain-secret', $model->password));
    }

    public function test_user_model_does_not_rehash_existing_hashed_password(): void
    {
        $hashedPassword = Hash::make('already-hashed-secret');

        $model = new User();
        $model->password = $hashedPassword;

        $this->assertSame($hashedPassword, $model->password);
        $this->assertTrue(Hash::check('already-hashed-secret', $model->password));
    }

    public function test_user_model_resolves_filament_name_from_full_name(): void
    {
        $model = new User([
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'email' => 'juan@example.test',
        ]);

        $this->assertSame('Juan Dela Cruz', $model->getFilamentName());
    }

    public function test_user_model_resolves_filament_name_from_email_when_name_is_missing(): void
    {
        $model = new User([
            'first_name' => null,
            'last_name' => null,
            'email' => 'fallback@example.test',
        ]);

        $this->assertSame('fallback@example.test', $model->getFilamentName());
    }

    public function test_user_model_allows_aics_staff_access_with_hyphenated_role_and_title_case_status(): void
    {
        $panel = \Mockery::mock(Panel::class);
        $panel->shouldReceive('getId')->once()->andReturn('aics-staff');

        $user = new User([
            'role' => 'AICS-STAFF',
            'status' => 'Active',
        ]);

        $this->assertTrue($user->canAccessPanel($panel));
    }

    public function test_user_model_supports_future_panels_with_matching_role(): void
    {
        $panel = \Mockery::mock(Panel::class);
        $panel->shouldReceive('getId')->once()->andReturn('review-team');

        $user = new User([
            'role' => 'review_team',
            'status' => 'active',
        ]);

        $this->assertTrue($user->canAccessPanel($panel));
    }
}
