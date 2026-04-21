<?php

namespace App\Filament\Pages;

use App\Services\AdminAnalyticsService;
use Filament\Pages\Page;

class Analytics extends Page
{
    protected static string $routePath = 'analytics';

    protected static ?string $navigationLabel = 'Analytics';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.admin.pages.analytics';

    public function getTitle(): string
    {
        return 'Admin Analytics';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $analytics = app(AdminAnalyticsService::class);

        return [
            'kpis' => $analytics->getKpis(),
            'latestActivities' => $analytics->paginateLatestActivities(10, 'latestActivitiesPage'),
            'unusualActivities' => $analytics->paginateUnusualActivities(10, 'unusualActivitiesPage'),
        ];
    }
}
