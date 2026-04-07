<?php

namespace App\Filament\AicsStaff\Widgets;

use App\Services\AicsStaffAnalyticsService;
use Filament\Widgets\Widget;

class SimpleKpiSectionsWidget extends Widget
{
    protected static bool $isLazy = false;

    protected string $view = 'filament.aics-staff.widgets.simple-kpi-sections-widget';

    protected int | string | array $columnSpan = 'full';

    /**
     * @return array{submitted:int,forwarded:int,returned:int,pending_code:int,forwarded_code:int,returned_code:int}
     */
    public function getKpis(): array
    {
        return app(AicsStaffAnalyticsService::class)->getSimpleKpis();
    }
}
