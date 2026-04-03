<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\AuditLog;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Http;
use Throwable;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user_id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('first_name')
                    ->label('First Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('last_name')
                    ->label('Last Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'active' ? 'success' : 'gray')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->options([
                        'admin' => 'Admin',
                        'aics_staff' => 'AICS Staff',
                        'mswd_officer' => 'MSWD Officer',
                        'mayor_office_staff' => 'Mayor Office Staff',
                        'accountant' => 'Accountant',
                        'treasurer' => 'Treasurer',
                    ]),

                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ]),
            ])
            ->recordActions([
                ActionGroup::make([
                    ActionGroup::make([
                        Action::make('resetPassword')
                            ->label('Reset Password')
                            ->icon(Heroicon::OutlinedKey)
                            ->modalHeading('Reset user password')
                            ->modalSubmitActionLabel('Confirm Reset')
                            ->form([
                                TextInput::make('password')
                                    ->label('New Password')
                                    ->password()
                                    ->revealable()
                                    ->required()
                                    ->minLength(8),

                                TextInput::make('password_confirmation')
                                    ->label('Confirm New Password')
                                    ->password()
                                    ->revealable()
                                    ->required()
                                    ->same('password')
                                    ->dehydrated(false),
                            ])
                            ->action(function (array $data, User $record): void {
                                $record->update([
                                    'password' => (string) ($data['password'] ?? ''),
                                ]);

                                $actor = auth()->user();

                                AuditLog::query()->create([
                                    'user_id' => is_int($actor?->user_id) ? $actor->user_id : 0,
                                    'module' => 'user_management',
                                    'action' => 'update',
                                    'description' => 'event=USER_PASSWORD_RESET; meta=' . json_encode([
                                        'target_user_id' => $record->user_id,
                                        'target_user_email' => $record->email,
                                    ], JSON_UNESCAPED_UNICODE),
                                    'ip_address' => request()->ip(),
                                    'timestamp' => now(),
                                ]);

                                Notification::make()
                                    ->title('Password reset successful')
                                    ->body("Password updated for {$record->email}.")
                                    ->success()
                                    ->send();
                            }),

                        Action::make('toggleStatus')
                            ->label(fn (User $record): string => $record->status === 'active' ? 'Deactivate Account' : 'Activate Account')
                            ->icon(fn (User $record): Heroicon => $record->status === 'active' ? Heroicon::OutlinedNoSymbol : Heroicon::OutlinedCheckCircle)
                            ->color(fn (User $record): string => $record->status === 'active' ? 'gray' : 'success')
                            ->requiresConfirmation()
                            ->action(function (User $record): void {
                                $nextStatus = $record->status === 'active' ? 'inactive' : 'active';

                                $record->update([
                                    'status' => $nextStatus,
                                ]);

                                $actor = auth()->user();

                                AuditLog::query()->create([
                                    'user_id' => is_int($actor?->user_id) ? $actor->user_id : 0,
                                    'module' => 'user_management',
                                    'action' => 'update',
                                    'description' => 'event=USER_STATUS_CHANGED; meta=' . json_encode([
                                        'target_user_id' => $record->user_id,
                                        'target_user_email' => $record->email,
                                        'status' => $nextStatus,
                                    ], JSON_UNESCAPED_UNICODE),
                                    'ip_address' => request()->ip(),
                                    'timestamp' => now(),
                                ]);

                                Notification::make()
                                    ->title('User status updated')
                                    ->body("{$record->email} is now {$nextStatus}.")
                                    ->success()
                                    ->send();
                            }),
                    ])
                        ->label('Edit User')
                        ->icon(Heroicon::OutlinedPencilSquare)
                        ->dropdownPlacement('right-top'),

                    DeleteAction::make()
                        ->label('Delete User')
                        ->requiresConfirmation()
                        ->successNotification(null)
                        ->action(function (User $record): void {
                            $email = (string) $record->email;
                            $targetUserId = $record->user_id;
                            $actor = auth()->user();
                            $supabaseDeleteResult = self::deleteSupabaseUserByEmail($email);

                            $auditLogCount = AuditLog::query()
                                ->where('user_id', $targetUserId)
                                ->count();

                            try {
                                if ($auditLogCount > 0) {
                                    AuditLog::query()
                                        ->where('user_id', $targetUserId)
                                        ->delete();
                                }

                                $record->delete();
                            } catch (QueryException) {
                                Notification::make()
                                    ->title('Unable to delete user')
                                    ->body("{$email} could not be deleted due to related records.")
                                    ->danger()
                                    ->send();

                                return;
                            }

                            AuditLog::query()->create([
                                'user_id' => is_int($actor?->user_id) ? $actor->user_id : 0,
                                'module' => 'user_management',
                                'action' => 'delete',
                                'description' => 'event=USER_DELETED; meta=' . json_encode([
                                    'target_user_id' => $targetUserId,
                                    'target_user_email' => $email,
                                    'purged_audit_logs' => $auditLogCount,
                                    'supabase_delete_code' => (string) ($supabaseDeleteResult['code'] ?? 'unknown'),
                                ], JSON_UNESCAPED_UNICODE),
                                'ip_address' => request()->ip(),
                                'timestamp' => now(),
                            ]);

                            Notification::make()
                                ->title('User deleted')
                                ->body("{$email} has been removed.")
                                ->success()
                                ->send();

                            if (($supabaseDeleteResult['ok'] ?? false) !== true && ($supabaseDeleteResult['code'] ?? null) !== 'not_found') {
                                Notification::make()
                                    ->title('Supabase cleanup warning')
                                    ->body((string) ($supabaseDeleteResult['message'] ?? 'User was deleted locally, but Supabase cleanup did not complete.'))
                                    ->warning()
                                    ->send();
                            }
                        }),
                ])
                    ->icon(Heroicon::EllipsisVertical)
                    ->tooltip('Actions')
                    ->label('Actions')
                    ->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('last_name')
            ->paginated([20, 50, 100])
            ->defaultPaginationPageOption(20);
    }

    /**
     * @return array{ok:bool,code:string,message:string}
     */
    private static function deleteSupabaseUserByEmail(string $email): array
    {
        $supabaseUrl = rtrim((string) config('supabase.url', ''), '/');
        $serviceRoleKey = (string) config('supabase.service_role_key', '');

        if ($supabaseUrl === '' || $serviceRoleKey === '') {
            return [
                'ok' => false,
                'code' => 'not_configured',
                'message' => 'Supabase admin provisioning is not configured, so remote account cleanup was skipped.',
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
                'code' => 'lookup_failed',
                'message' => 'Supabase lookup failed during remote account cleanup.',
            ];
        }

        if (! $lookupResponse->successful()) {
            return [
                'ok' => false,
                'code' => 'lookup_failed',
                'message' => 'Supabase lookup failed during remote account cleanup.',
            ];
        }

        $payload = $lookupResponse->json();
        $users = [];

        if (is_array($payload)) {
            if (isset($payload['users']) && is_array($payload['users'])) {
                $users = $payload['users'];
            } elseif (array_is_list($payload)) {
                $users = $payload;
            }
        }

        $matchedUserId = '';
        foreach ($users as $user) {
            if (! is_array($user)) {
                continue;
            }

            if (strtolower((string) ($user['email'] ?? '')) === strtolower($email)) {
                $matchedUserId = (string) ($user['id'] ?? '');

                break;
            }
        }

        if ($matchedUserId === '') {
            return [
                'ok' => true,
                'code' => 'not_found',
                'message' => 'No matching Supabase Auth account found for cleanup.',
            ];
        }

        try {
            $deleteResponse = Http::timeout(10)
                ->withHeaders([
                    'apikey' => $serviceRoleKey,
                    'Authorization' => 'Bearer ' . $serviceRoleKey,
                ])
                ->acceptJson()
                ->delete($supabaseUrl . '/auth/v1/admin/users/' . $matchedUserId);
        } catch (Throwable) {
            return [
                'ok' => false,
                'code' => 'delete_failed',
                'message' => 'Supabase delete request failed during remote account cleanup.',
            ];
        }

        if (! $deleteResponse->successful()) {
            return [
                'ok' => false,
                'code' => 'delete_failed',
                'message' => 'Supabase delete request was rejected during remote account cleanup.',
            ];
        }

        return [
            'ok' => true,
            'code' => 'deleted',
            'message' => 'Supabase Auth account deleted.',
        ];
    }
}
