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

                TextColumn::make('event_code')
                    ->label('Event')
                    ->state(static function ($record): string {
                        $description = is_string($record->description ?? null)
                            ? $record->description
                            : '';

                        if (! str_contains($description, 'event=')) {
                            return '-';
                        }

                        $afterEvent = explode('event=', $description, 2)[1] ?? '';
                        $eventCode = trim(explode(';', $afterEvent, 2)[0]);

                        return $eventCode !== '' ? $eventCode : '-';
                    })
                    ->badge()
                    ->toggleable(),

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
                        'create' => 'Create',
                        'update' => 'Update',
                        'delete' => 'Delete',
                        'login' => 'Login',
                        'logout' => 'Logout',
                        'configure' => 'Configure',
                    ]),
            ])
            ->defaultSort('timestamp', 'desc')
            ->paginated([20, 50, 100])
            ->defaultPaginationPageOption(20);
    }
}
