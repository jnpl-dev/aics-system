<?php

namespace Tests\Unit;

use App\Models\User;
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
}
