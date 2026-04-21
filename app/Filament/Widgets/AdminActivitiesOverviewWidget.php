<?php

namespace App\Filament\Widgets;

use App\Services\AdminAnalyticsService;
use Filament\Widgets\Widget;

class AdminActivitiesOverviewWidget extends Widget
{
    protected static bool $isLazy = false;

    protected string $view = 'filament.admin.widgets.admin-activities-overview-widget';

    protected int | string | array $columnSpan = 'full';

    /**
     * @return list<array{log_id:int,user:string,action:string,module_page:string,ip_address:string,date_time:string}>
     */
    public function getLatestActivities(): array
    {
        return app(AdminAnalyticsService::class)->getLatestActivities(5);
    }

    /**
     * @return list<array{user:string,flagged_reason:string,attempt_count:int,last_attempt:string,last_attempt_at:string,severity:string,severity_tone:string}>
     */
    public function getUnusualActivities(): array
    {
        return app(AdminAnalyticsService::class)->getUnusualActivities(5);
    }
}
