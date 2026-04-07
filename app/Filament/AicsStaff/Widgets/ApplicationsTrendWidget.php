<?php

namespace App\Filament\AicsStaff\Widgets;

use App\Services\AicsStaffAnalyticsService;
use Filament\Widgets\Widget;

class ApplicationsTrendWidget extends Widget
{
    protected static bool $isLazy = false;

    protected string $view = 'filament.aics-staff.widgets.applications-trend-widget';

    protected int | string | array $columnSpan = 'full';

    /**
     * @return array{labels:list<string>,values:list<int>,period:string}
     */
    public function getTrendData(): array
    {
        return app(AicsStaffAnalyticsService::class)->getApplicationTrend('week');
    }
}
