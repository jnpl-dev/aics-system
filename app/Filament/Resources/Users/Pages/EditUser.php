<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\AuditLog;
use App\Models\User;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $currentUserId = (string) auth()->id();
        $recordUserId = (string) $this->getRecord()->getKey();

        if ($currentUserId === $recordUserId) {
            $this->redirect(UserResource::getUrl('index'));
        }

        return $data;
    }

    protected function getRedirectUrl(): ?string
    {
        return UserResource::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var User $record */
        $operation = (string) ($data['edit_operation'] ?? '');
        $newPassword = (string) ($data['password'] ?? '');
        $requestedStatus = strtolower((string) ($data['status'] ?? ''));
        $originalStatus = (string) $record->status;
        $updateData = [];

        if ($operation === 'reset_password') {
            if ($newPassword === '') {
                throw ValidationException::withMessages([
                    'password' => 'Please enter a new password.',
                ]);
            }

            $updateData['password'] = $newPassword;
        }

        if ($operation === 'account_status') {
            if (! in_array($requestedStatus, ['active', 'inactive'], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Please select a valid account status.',
                ]);
            }

            $updateData['status'] = $requestedStatus;
        }

        if ($updateData === []) {
            return $record;
        }

        $record->update($updateData);

        $actor = auth()->user();

        if (array_key_exists('password', $updateData)) {
            AuditLog::query()->create([
                'user_id' => is_int($actor?->user_id) ? $actor->user_id : 0,
                'module' => 'user_management',
                'action' => 'update',
                'description' => 'event=USER_PASSWORD_RESET; meta='.json_encode([
                    'target_user_id' => $record->user_id,
                    'target_user_email' => $record->email,
                ], JSON_UNESCAPED_UNICODE),
                'ip_address' => request()->ip(),
                'timestamp' => now(),
            ]);
        }

        if (array_key_exists('status', $updateData) && $updateData['status'] !== $originalStatus) {
            AuditLog::query()->create([
                'user_id' => is_int($actor?->user_id) ? $actor->user_id : 0,
                'module' => 'user_management',
                'action' => 'update',
                'description' => 'event=USER_STATUS_CHANGED; meta='.json_encode([
                    'target_user_id' => $record->user_id,
                    'target_user_email' => $record->email,
                    'status' => $updateData['status'],
                ], JSON_UNESCAPED_UNICODE),
                'ip_address' => request()->ip(),
                'timestamp' => now(),
            ]);
        }

        return $record;
    }
}
