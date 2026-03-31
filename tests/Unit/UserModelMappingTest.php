<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class UserModelMappingTest extends TestCase
{
    public function test_user_model_uses_legacy_user_table_and_primary_key(): void
    {
        $model = new User();

        $this->assertSame('user', $model->getTable());
        $this->assertSame('user_id', $model->getKeyName());
    }
}
