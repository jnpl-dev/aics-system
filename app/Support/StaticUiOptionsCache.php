<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

final class StaticUiOptionsCache
{
    /**
     * @return array<string, string>
     */
    public static function exportStorageDiskOptions(string $scope = 'shared'): array
    {
        return Cache::rememberForever("{$scope}:static:export_modal:storage_disks", static fn (): array => [
            'local' => 'Local Folder',
        ]);
    }

    /**
     * @return array<string, string>
     */
    public static function adminUserRoles(): array
    {
        return Cache::rememberForever('admin:static:new_user:roles', static fn (): array => [
            'admin' => 'Admin',
            'aics_staff' => 'AICS Staff',
            'mswd_officer' => 'MSWD Officer',
            'mayor_office_staff' => 'Mayor Office Staff',
            'accountant' => 'Accountant',
            'treasurer' => 'Treasurer',
        ]);
    }

    /**
     * @return array<string, string>
     */
    public static function adminUserStatuses(): array
    {
        return Cache::rememberForever('admin:static:new_user:statuses', static fn (): array => [
            'active' => 'Active',
            'inactive' => 'Inactive',
        ]);
    }

    /**
     * @return array<string, string>
     */
    public static function adminEditOperations(): array
    {
        return Cache::rememberForever('admin:static:new_user:edit_operations', static fn (): array => [
            'reset_password' => 'Reset Password',
            'account_status' => 'Activate / Deactivate Account',
        ]);
    }
}
