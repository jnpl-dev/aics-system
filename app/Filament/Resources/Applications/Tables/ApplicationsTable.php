<?php

namespace App\Filament\Resources\Applications\Tables;

use App\Filament\Resources\Applications\ApplicationResource;
use App\Models\Application;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\PaginationMode;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('application_id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('reference_code')
                    ->label('Reference Code')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'submitted', 'pending_assistance_code', 'pending_voucher', 'pending_cheque' => 'primary',
                        'additional_docs_required', 'resubmission_required', 'code_adjustment_required', 'voucher_adjustment_required', 'cheque_on_hold' => 'warning',
                        'forwarded_to_mswdo', 'forwarded_to_mayors_office', 'forwarded_to_accounting', 'cheque_ready' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(static fn (string $state): string => match ($state) {
                        'additional_docs_required' => 'Additional Documents Required',
                        default => str($state)->replace('_', ' ')->title()->toString(),
                    })
                    ->sortable(),

                TextColumn::make('applicant_last_name')
                    ->label('Applicant Last Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('applicant_first_name')
                    ->label('Applicant First Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('applicant_phone')
                    ->label('Applicant Phone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('beneficiary_last_name')
                    ->label('Beneficiary Last Name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('beneficiary_first_name')
                    ->label('Beneficiary First Name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('submitted_at')
                    ->label('Submitted At')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),

                TextColumn::make('reviewed_at')
                    ->label('Reviewed At')
                    ->dateTime('M d, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('applicant_sex')
                    ->label('Applicant Sex')
                    ->options([
                        'male' => 'Male',
                        'female' => 'Female',
                    ]),

                SelectFilter::make('beneficiary_sex')
                    ->label('Beneficiary Sex')
                    ->options([
                        'male' => 'Male',
                        'female' => 'Female',
                    ]),

                Filter::make('submitted_at')
                    ->label('Submitted Date')
                    ->schema([
                        DatePicker::make('submitted_from')
                            ->label('Submitted From'),
                        DatePicker::make('submitted_until')
                            ->label('Submitted Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                filled($data['submitted_from'] ?? null),
                                fn (Builder $query): Builder => $query->whereDate('submitted_at', '>=', (string) $data['submitted_from']),
                            )
                            ->when(
                                filled($data['submitted_until'] ?? null),
                                fn (Builder $query): Builder => $query->whereDate('submitted_at', '<=', (string) $data['submitted_until']),
                            );
                    }),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->persistSearchInSession()
            ->persistFiltersInSession()
            ->persistSortInSession()
            ->searchDebounce('250ms')
            ->paginationMode(PaginationMode::Simple)
            ->recordActions([
                Action::make('review')
                    ->label('Review')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('primary')
                    ->visible(fn (Application $record): bool => self::isPendingStatus((string) $record->status))
                    ->url(fn (Application $record): string => ApplicationResource::getUrl('review', ['record' => $record])),

                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->visible(fn (Application $record): bool => self::isForwardedOrReturnedStatus((string) $record->status))
                    ->url(fn (Application $record): string => ApplicationResource::getUrl('view', ['record' => $record])),
            ])
            ->recordActionsColumnLabel('')
            ->paginated([20, 50, 100])
            ->defaultPaginationPageOption(20);
    }

    private static function isPendingStatus(string $status): bool
    {
        return in_array($status, ['submitted', 'resubmission_required'], true);
    }

    private static function isForwardedOrReturnedStatus(string $status): bool
    {
        return in_array($status, [
            'forwarded_to_mswdo',
            'forwarded_to_mayors_office',
            'forwarded_to_accounting',
            'additional_docs_required',
            'resubmission_required',
            'code_adjustment_required',
            'voucher_adjustment_required',
            'pending_assistance_code',
            'pending_voucher',
            'pending_cheque',
            'cheque_on_hold',
            'cheque_ready',
        ], true);
    }
}
