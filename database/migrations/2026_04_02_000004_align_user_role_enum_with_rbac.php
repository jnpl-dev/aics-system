<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("\n            UPDATE user\n            SET role = 'mayor_office_staff'\n            WHERE role = 'mayors_office'\n        ");

        DB::statement("\n            ALTER TABLE user\n            MODIFY role ENUM(\n                'aics_staff',\n                'mswd_officer',\n                'mayor_office_staff',\n                'accountant',\n                'treasurer',\n                'admin',\n                'system_admin'\n            ) NOT NULL\n        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("\n            UPDATE user\n            SET role = 'mayors_office'\n            WHERE role = 'mayor_office_staff'\n        ");

        DB::statement("\n            ALTER TABLE user\n            MODIFY role ENUM(\n                'aics_staff',\n                'mswd_officer',\n                'mayors_office',\n                'accountant',\n                'treasurer',\n                'admin'\n            ) NOT NULL\n        ");
    }
};
