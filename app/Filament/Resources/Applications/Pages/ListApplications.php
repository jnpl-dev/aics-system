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
                    'under_review',
                ])),

            'forwarded' => Tab::make('Forwarded')
                ->badge($this->countForwarded())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereIn('status', [
                    'forwarded_to_mswd',
                    'forwarded_to_mayor',
                    'forwarded_to_accounting',
                    'forwarded_to_treasury',
                ])),

            'returned' => Tab::make('Returned')
                ->badge($this->countReturned())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereIn('status', [
                    'pending_additional_docs',
                    'resubmission_required',
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
            ->whereIn('status', ['submitted', 'under_review'])
            ->count();
    }

    private function countForwarded(): int
    {
        return Application::query()
            ->whereIn('status', ['forwarded_to_mswd', 'forwarded_to_mayor', 'forwarded_to_accounting', 'forwarded_to_treasury'])
            ->count();
    }

    private function countReturned(): int
    {
        return Application::query()
            ->whereIn('status', ['pending_additional_docs', 'resubmission_required'])
            ->count();
    }
}
