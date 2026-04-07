<?php

namespace App\Filament\AicsStaff\Pages;

use App\Filament\AicsStaff\Widgets\ApplicationsTrendWidget;
use App\Filament\AicsStaff\Widgets\NewOldApplicationsListWidget;
use App\Filament\AicsStaff\Widgets\SimpleKpiSectionsWidget;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static string $routePath = '/';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?int $navigationSort = 1;

    public function getTitle(): string
    {
        return 'AICS Staff Dashboard';
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
            SimpleKpiSectionsWidget::class,
            NewOldApplicationsListWidget::class,
            ApplicationsTrendWidget::class,
        ];
    }
}
