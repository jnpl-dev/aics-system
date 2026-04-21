<?php

namespace App\Filament\Widgets;

use App\Services\AdminAnalyticsService;
use Filament\Widgets\Widget;

class AdminKpiSummaryWidget extends Widget
{
    protected static bool $isLazy = false;

    protected string $view = 'filament.admin.widgets.admin-kpi-summary-widget';

    protected int | string | array $columnSpan = 'full';

    /**
     * @return array{active_users:int,inactive_users:int,total_users:int}
     */
    public function getKpis(): array
    {
        return app(AdminAnalyticsService::class)->getKpis();
    }
}
