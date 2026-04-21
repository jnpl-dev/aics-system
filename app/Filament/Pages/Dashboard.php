<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AdminActivitiesOverviewWidget;
use App\Filament\Widgets\AdminKpiSummaryWidget;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static string $routePath = '/';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?int $navigationSort = 1;

    public function getTitle(): string
    {
        return 'Admin Dashboard';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewAnalytics')
                ->label('View Full Analytics')
                ->icon('heroicon-o-chart-bar')
                ->url(Analytics::getUrl()),
        ];
    }

    public function getWidgets(): array
    {
        return [
            AdminKpiSummaryWidget::class,
            AdminActivitiesOverviewWidget::class,
        ];
    }
}
