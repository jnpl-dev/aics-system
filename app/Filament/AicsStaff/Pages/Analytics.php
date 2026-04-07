<?php

namespace App\Filament\AicsStaff\Pages;

use App\Services\AicsStaffAnalyticsService;
use Filament\Pages\Page;

class Analytics extends Page
{
    protected static string $routePath = 'analytics';

    protected static ?string $navigationLabel = 'Analytics';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.aics-staff.pages.analytics';

    public string $trendPeriod = 'week';

    public function getTitle(): string
    {
        return 'AICS Staff Analytics';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $analytics = app(AicsStaffAnalyticsService::class);

        return [
            'kpis' => $analytics->getSimpleKpis(),
            'newPendingList' => $analytics->getPendingReviewList('new', 5),
            'oldPendingList' => $analytics->getPendingReviewList('old', 5),
            'applicationsTrend' => $analytics->getApplicationTrend($this->trendPeriod),
        ];
    }
}
