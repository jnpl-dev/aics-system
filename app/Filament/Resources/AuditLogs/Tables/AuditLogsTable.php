<?php

namespace App\Filament\Resources\AuditLogs\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AuditLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('log_id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('timestamp')
                    ->label('Timestamp')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),

                TextColumn::make('module')
                    ->label('Module')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('action')
                    ->label('Action')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Description')
                    ->limit(80)
                    ->tooltip(fn (?string $state): ?string => filled($state) ? $state : null)
                    ->searchable(),

                TextColumn::make('user_id')
                    ->label('User ID')
                    ->sortable(),

                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('action')
                    ->options([
                        'AUTH_LOGIN_SUCCESS' => 'Auth Login Success',
                        'AUTH_LOGIN_FAILED' => 'Auth Login Failed',
                        'AUTH_LOGOUT' => 'Auth Logout',
                        'AUTH_SESSION_EXPIRED' => 'Auth Session Expired',
                        'OTP_GENERATED_SENT' => 'OTP Generated Sent',
                        'OTP_RESEND' => 'OTP Resend',
                        'OTP_VERIFIED' => 'OTP Verified',
                        'OTP_FAILED' => 'OTP Failed',
                        'OTP_EXPIRED' => 'OTP Expired',
                    ]),
            ])
            ->defaultSort('timestamp', 'desc')
            ->paginated([20, 50, 100])
            ->defaultPaginationPageOption(20);
    }
}
