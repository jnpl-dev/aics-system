<?php

namespace App\Filament\AicsStaff\Widgets;

use App\Services\AicsStaffAnalyticsService;
use Filament\Widgets\Widget;

class NewOldApplicationsListWidget extends Widget
{
    protected string $view = 'filament.aics-staff.widgets.new-old-applications-list-widget';

    protected int | string | array $columnSpan = 'full';

    /**
     * @return list<array{application_id:int,reference_code:string,applicant_name:string,submitted_at:string,age_hours:int,status:string,status_label:string}>
     */
    public function getNewApplications(): array
    {
        return app(AicsStaffAnalyticsService::class)->getPendingReviewList('new', 5);
    }

    /**
     * @return list<array{application_id:int,reference_code:string,applicant_name:string,submitted_at:string,age_hours:int,status:string,status_label:string}>
     */
    public function getOldApplications(): array
    {
        return app(AicsStaffAnalyticsService::class)->getPendingReviewList('old', 5);
    }
}
