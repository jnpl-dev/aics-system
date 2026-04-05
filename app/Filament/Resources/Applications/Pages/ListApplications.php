<?php

namespace App\Filament\Resources\Applications\Pages;

use App\Filament\Resources\Applications\ApplicationResource;
use App\Models\Application;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListApplications extends ListRecords
{
    protected static string $resource = ApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    /**
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        return [
            'pending' => Tab::make('Pending')
                ->badge($this->countPending())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereIn('status', [
                    'submitted',
                    'pending_assistance_code',
                    'pending_voucher',
                    'pending_cheque',
                    'cheque_on_hold',
                    'cheque_ready',
                ])),

            'forwarded' => Tab::make('Forwarded')
                ->badge($this->countForwarded())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereIn('status', [
                    'forwarded_to_mswdo',
                    'forwarded_to_mayors_office',
                    'forwarded_to_accounting',
                ])),

            'returned' => Tab::make('Returned')
                ->badge($this->countReturned())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereIn('status', [
                    'additional_docs_required',
                    'resubmission_required',
                    'code_adjustment_required',
                    'voucher_adjustment_required',
                ])),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'pending';
    }

    private function countPending(): int
    {
        return Application::query()
            ->whereIn('status', ['submitted', 'pending_assistance_code', 'pending_voucher', 'pending_cheque', 'cheque_on_hold', 'cheque_ready'])
            ->count();
    }

    private function countForwarded(): int
    {
        return Application::query()
            ->whereIn('status', ['forwarded_to_mswdo', 'forwarded_to_mayors_office', 'forwarded_to_accounting'])
            ->count();
    }

    private function countReturned(): int
    {
        return Application::query()
            ->whereIn('status', ['additional_docs_required', 'resubmission_required', 'code_adjustment_required', 'voucher_adjustment_required'])
            ->count();
    }
}
