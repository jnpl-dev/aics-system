<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Password;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('first_name')
                    ->label('First Name')
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->required()
                    ->maxLength(120),

                TextInput::make('last_name')
                    ->label('Last Name')
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->required()
                    ->maxLength(120),

                TextInput::make('email')
                    ->label('Email')
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                Hidden::make('edit_operation')
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->default('create'),

                ToggleButtons::make('edit_operation')
                    ->label('Edit User Option')
                    ->visible(fn (string $operation): bool => $operation === 'edit')
                    ->inline()
                    ->default('reset_password')
                    ->required(fn (string $operation): bool => $operation === 'edit')
                    ->options([
                        'reset_password' => 'Reset Password',
                        'account_status' => 'Activate / Deactivate Account',
                    ]),

                TextInput::make('password')
                    ->label(fn (string $operation): string => $operation === 'edit' ? 'Reset Password' : 'Password')
                    ->visible(fn (string $operation, Get $get): bool => $operation === 'create' || $get('edit_operation') === 'reset_password')
                    ->password()
                    ->revealable()
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (string $operation, Get $get): bool => $operation === 'create' || $get('edit_operation') === 'reset_password')
                    ->rule(Password::min(8)->letters()->numbers()),

                TextInput::make('password_confirmation')
                    ->label('Confirm New Password')
                    ->visible(fn (string $operation, Get $get): bool => $operation === 'edit' && $get('edit_operation') === 'reset_password')
                    ->password()
                    ->revealable()
                    ->dehydrated(false)
                    ->required(fn (string $operation, Get $get): bool => $operation === 'edit' && $get('edit_operation') === 'reset_password')
                    ->same('password'),

                Select::make('role')
                    ->label('Role')
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->required()
                    ->options([
                        'admin' => 'Admin',
                        'aics_staff' => 'AICS Staff',
                        'mswd_officer' => 'MSWD Officer',
                        'mayor_office_staff' => 'Mayor Office Staff',
                        'accountant' => 'Accountant',
                        'treasurer' => 'Treasurer',
                    ])
                    ->searchable(),

                ToggleButtons::make('status')
                    ->label('Status')
                    ->visible(fn (string $operation, Get $get): bool => $operation === 'edit' && $get('edit_operation') === 'account_status')
                    ->inline()
                    ->default('active')
                    ->required(fn (string $operation, Get $get): bool => $operation === 'edit' && $get('edit_operation') === 'account_status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ]),
            ]);
    }
}
