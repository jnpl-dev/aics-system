<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\AuditLog;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Throwable;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getCreatedNotification(): ?Notification
    {
        $record = $this->getRecord();
        $email = $record instanceof User ? (string) $record->email : 'user account';

        return Notification::make()
            ->success()
            ->title('User created')
            ->body("{$email} was created successfully.");
    }

    protected function getRedirectUrl(): string
    {
        return UserResource::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $sanitizeName = static fn (string $value): string => trim((string) preg_replace('/\s+/', ' ', preg_replace('/[^\pL\s\'\-]/u', '', $value) ?? ''));

        $data['first_name'] = $sanitizeName((string) ($data['first_name'] ?? ''));
        $data['last_name'] = $sanitizeName((string) ($data['last_name'] ?? ''));
        $data['email'] = strtolower(trim((string) ($data['email'] ?? '')));
        $data['status'] = 'active';

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $enforceSupabaseProvisioning = (bool) config('supabase.enforce_admin_provisioning', false);

        $supabaseProvisionResult = $this->provisionSupabaseUser(
            (string) $data['email'],
            (string) $data['password']
        );

        if (($supabaseProvisionResult['ok'] ?? false) !== true) {
            $failureCode = (string) ($supabaseProvisionResult['code'] ?? 'unknown');
            $failureMessage = (string) ($supabaseProvisionResult['message'] ?? 'Unable to create login credentials for this email.');

            if (! $enforceSupabaseProvisioning) {
                Notification::make()
                    ->title('Created locally only')
                    ->body("Supabase provisioning failed ({$failureCode}). This account was created in the local database only. Details: {$failureMessage}")
                    ->warning()
                    ->persistent()
                    ->send();
            } else {
                throw ValidationException::withMessages([
                    'email' => $failureMessage,
                ]);
            }
        }

        $supabaseUserId = (string) ($supabaseProvisionResult['user_id'] ?? '');

        try {
            /** @var User $user */
            $user = DB::transaction(function () use ($data): User {
                return User::query()->create([
                    'first_name' => (string) $data['first_name'],
                    'last_name' => (string) $data['last_name'],
                    'email' => (string) $data['email'],
                    'password' => (string) $data['password'],
                    'role' => (string) $data['role'],
                    'status' => 'active',
                ]);
            });
        } catch (Throwable) {
            if ($supabaseUserId !== '') {
                $this->deleteSupabaseUser($supabaseUserId);
            }

            throw ValidationException::withMessages([
                'email' => 'Unable to save user account. Please try again.',
            ]);
        }

        $actor = auth()->user();

        AuditLog::query()->create([
            'user_id' => is_int($actor?->user_id) ? $actor->user_id : 0,
            'module' => 'user_management',
            'action' => 'create',
            'description' => 'event=USER_CREATED; meta=' . json_encode([
                'operation' => 'user_created',
                'created_user_id' => $user->user_id,
                'created_user_email' => $user->email,
                'supabase_user_id' => $supabaseUserId,
            ], JSON_UNESCAPED_UNICODE),
            'ip_address' => request()->ip(),
            'timestamp' => now('Asia/Manila'),
        ]);

        return $user;
    }

    /**
     * @return array{ok:bool,message:string,code?:string,user_id?:string}
     */
    private function provisionSupabaseUser(string $email, string $password): array
    {
        $supabaseUrl = rtrim((string) config('supabase.url', ''), '/');
        $serviceRoleKey = (string) config('supabase.service_role_key', '');

        if ($supabaseUrl === '' || $serviceRoleKey === '') {
            return [
                'ok' => false,
                'message' => 'Supabase admin provisioning is not configured. Please set SUPABASE_URL and SUPABASE_SERVICE_ROLE_KEY.',
                'code' => 'not_configured',
            ];
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'apikey' => $serviceRoleKey,
                    'Authorization' => 'Bearer ' . $serviceRoleKey,
                ])
                ->acceptJson()
                ->post($supabaseUrl . '/auth/v1/admin/users', [
                    'email' => $email,
                    'password' => $password,
                    'email_confirm' => true,
                ]);
        } catch (Throwable) {
            return [
                'ok' => false,
                'message' => 'Unable to reach Supabase Auth. Please try again in a moment.',
                'code' => 'unreachable',
            ];
        }

        $payload = $response->json();

        if (! $response->successful()) {
            $message = is_array($payload)
                ? (string) ($payload['msg'] ?? $payload['message'] ?? $payload['error_description'] ?? $payload['error'] ?? 'Failed to create Supabase user.')
                : 'Failed to create Supabase user.';

            if (str_contains(strtolower($message), 'already')) {
                $reconciled = $this->reconcileExistingSupabaseUser($email, $password);

                if (($reconciled['ok'] ?? false) === true) {
                    return $reconciled;
                }

                $message = (string) ($reconciled['message'] ?? 'Email already exists in Supabase Auth. Use a different email or reset that account password.');

                return [
                    'ok' => false,
                    'message' => $message,
                    'code' => 'already_exists',
                ];
            }

            return [
                'ok' => false,
                'message' => $message,
                'code' => 'api_error',
            ];
        }

        $userId = '';
        if (is_array($payload)) {
            $userId = (string) ($payload['id'] ?? $payload['user']['id'] ?? '');
        }

        return [
            'ok' => true,
            'message' => 'Supabase user created.',
            'user_id' => $userId,
        ];
    }

    /**
     * @return array{ok:bool,message:string,code?:string,user_id?:string}
     */
    private function reconcileExistingSupabaseUser(string $email, string $password): array
    {
        $supabaseUrl = rtrim((string) config('supabase.url', ''), '/');
        $serviceRoleKey = (string) config('supabase.service_role_key', '');

        if ($supabaseUrl === '' || $serviceRoleKey === '') {
            return [
                'ok' => false,
                'message' => 'Supabase admin provisioning is not configured. Please set SUPABASE_URL and SUPABASE_SERVICE_ROLE_KEY.',
                'code' => 'not_configured',
            ];
        }

        try {
            $lookupResponse = Http::timeout(10)
                ->withHeaders([
                    'apikey' => $serviceRoleKey,
                    'Authorization' => 'Bearer ' . $serviceRoleKey,
                ])
                ->acceptJson()
                ->get($supabaseUrl . '/auth/v1/admin/users', [
                    'email' => $email,
                    'per_page' => 1,
                    'page' => 1,
                ]);
        } catch (Throwable) {
            return [
                'ok' => false,
                'message' => 'Supabase user exists, but lookup failed. Please retry or reset the Supabase password manually.',
                'code' => 'lookup_failed',
            ];
        }

        $lookupPayload = $lookupResponse->json();

        if (! $lookupResponse->successful()) {
            return [
                'ok' => false,
                'message' => 'Supabase user exists, but lookup failed. Please retry or reset the Supabase password manually.',
                'code' => 'lookup_failed',
            ];
        }

        $users = [];
        if (is_array($lookupPayload)) {
            if (isset($lookupPayload['users']) && is_array($lookupPayload['users'])) {
                $users = $lookupPayload['users'];
            } elseif (array_is_list($lookupPayload)) {
                $users = $lookupPayload;
            }
        }

        $matchedUser = null;
        foreach ($users as $user) {
            if (! is_array($user)) {
                continue;
            }

            if (strtolower((string) ($user['email'] ?? '')) === strtolower($email)) {
                $matchedUser = $user;

                break;
            }
        }

        $existingUserId = is_array($matchedUser) ? (string) ($matchedUser['id'] ?? '') : '';

        if ($existingUserId === '') {
            return [
                'ok' => false,
                'message' => 'Supabase user exists, but user ID could not be resolved for password update.',
                'code' => 'lookup_failed',
            ];
        }

        try {
            $updateResponse = Http::timeout(10)
                ->withHeaders([
                    'apikey' => $serviceRoleKey,
                    'Authorization' => 'Bearer ' . $serviceRoleKey,
                ])
                ->acceptJson()
                ->put($supabaseUrl . '/auth/v1/admin/users/' . $existingUserId, [
                    'password' => $password,
                    'email_confirm' => true,
                ]);
        } catch (Throwable) {
            return [
                'ok' => false,
                'message' => 'Supabase user exists, but password update failed. Please reset the Supabase password manually.',
                'code' => 'update_failed',
            ];
        }

        if (! $updateResponse->successful()) {
            return [
                'ok' => false,
                'message' => 'Supabase user exists, but password update failed. Please reset the Supabase password manually.',
                'code' => 'update_failed',
            ];
        }

        return [
            'ok' => true,
            'message' => 'Existing Supabase user reconciled and password updated.',
            'code' => 'reconciled_existing',
            'user_id' => $existingUserId,
        ];
    }

    private function deleteSupabaseUser(string $userId): void
    {
        $supabaseUrl = rtrim((string) config('supabase.url', ''), '/');
        $serviceRoleKey = (string) config('supabase.service_role_key', '');

        if ($supabaseUrl === '' || $serviceRoleKey === '' || $userId === '') {
            return;
        }

        Http::timeout(10)
            ->withHeaders([
                'apikey' => $serviceRoleKey,
                'Authorization' => 'Bearer ' . $serviceRoleKey,
            ])
            ->acceptJson()
            ->delete($supabaseUrl . '/auth/v1/admin/users/' . $userId);
    }
}
