<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
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
                    ->required()
                    ->maxLength(120),

                TextInput::make('last_name')
                    ->label('Last Name')
                    ->required()
                    ->maxLength(120),

                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->revealable()
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->rule(Password::min(8)->letters()->numbers()),

                Select::make('role')
                    ->label('Role')
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
                    ->inline()
                    ->default('active')
                    ->required()
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ]),
            ]);
    }
}
