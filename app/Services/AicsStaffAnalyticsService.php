<?php

namespace App\Services;

use App\Support\ApplicationStatuses;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB as FacadesDB;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AicsStaffAnalyticsService
{
    /**
     * @return array{applications:array<string,int>,assistance_code:array<string,int>,new_pending:int,old_pending:int}
     */
    public function getDashboardSummary(): array
    {
        $snapshot = $this->getQueueSnapshot();

        return [
            'applications' => [
                ApplicationStatuses::SUBMITTED => $snapshot[ApplicationStatuses::SUBMITTED] ?? 0,
                ApplicationStatuses::RESUBMISSION_REQUIRED => $snapshot[ApplicationStatuses::RESUBMISSION_REQUIRED] ?? 0,
                ApplicationStatuses::FORWARDED_TO_MSWDO => $snapshot[ApplicationStatuses::FORWARDED_TO_MSWDO] ?? 0,
            ],
            'assistance_code' => [
                ApplicationStatuses::PENDING_ASSISTANCE_CODE => $snapshot[ApplicationStatuses::PENDING_ASSISTANCE_CODE] ?? 0,
                ApplicationStatuses::FORWARDED_TO_MAYORS_OFFICE => $this->getStatusCount(ApplicationStatuses::FORWARDED_TO_MAYORS_OFFICE),
                ApplicationStatuses::CODE_ADJUSTMENT_REQUIRED => $this->getStatusCount(ApplicationStatuses::CODE_ADJUSTMENT_REQUIRED),
            ],
            'new_pending' => $this->getNewPendingCount(),
            'old_pending' => $this->getOldPendingCount(),
        ];
    }

    /**
     * @return array<string, int>
     */
    public function getQueueSnapshot(): array
    {
        return Cache::remember('aics_staff:analytics:queue_snapshot', now()->addMinute(), function (): array {
            if (! Schema::hasTable('application')) {
                return [];
            }

            /** @var Collection<int, object{status:string,count:int}> $counts */
            $counts = DB::table('application')
                ->select('status', DB::raw('COUNT(*) as count'))
                ->whereIn('status', ApplicationStatuses::aicsPrimaryQueue())
                ->groupBy('status')
                ->get();

            $result = array_fill_keys(ApplicationStatuses::aicsPrimaryQueue(), 0);

            foreach ($counts as $row) {
                $status = (string) $row->status;
                $result[$status] = (int) $row->count;
            }

            return $result;
        });
    }

    public function getStatusCount(string $status): int
    {
        return (int) DB::table('application')
            ->where('status', $status)
            ->count();
    }

    public function getNewPendingCount(): int
    {
        if (! Schema::hasTable('application')) {
            return 0;
        }

        return (int) DB::table('application')
            ->whereIn('status', ApplicationStatuses::pendingReview())
            ->whereDate('submitted_at', now()->toDateString())
            ->count();
    }

    public function getOldPendingCount(): int
    {
        if (! Schema::hasTable('application')) {
            return 0;
        }

        return (int) DB::table('application')
            ->whereIn('status', ApplicationStatuses::pendingReview())
            ->whereDate('submitted_at', '<', now()->toDateString())
            ->count();
    }

    /**
     * @return array{submitted:int,forwarded:int,returned:int,pending_code:int,forwarded_code:int,returned_code:int}
     */
    public function getSimpleKpis(): array
    {
        return [
            'submitted' => $this->getStatusCount(ApplicationStatuses::SUBMITTED),
            'forwarded' => $this->getStatusCount(ApplicationStatuses::FORWARDED_TO_MSWDO),
            'returned' => $this->getStatusCount(ApplicationStatuses::RESUBMISSION_REQUIRED),
            'pending_code' => $this->getStatusCount(ApplicationStatuses::PENDING_ASSISTANCE_CODE),
            'forwarded_code' => $this->getStatusCount(ApplicationStatuses::FORWARDED_TO_MAYORS_OFFICE),
            'returned_code' => $this->getStatusCount(ApplicationStatuses::CODE_ADJUSTMENT_REQUIRED),
        ];
    }

    /**
     * @return array{labels:list<string>,values:list<int>,period:string}
     */
    public function getApplicationTrend(string $period = 'week'): array
    {
        if (! Schema::hasTable('application')) {
            return [
                'labels' => [],
                'values' => [],
                'period' => $period,
            ];
        }

        if ($period === 'year') {
            $labels = [];
            $values = [];

            for ($month = 1; $month <= 12; $month++) {
                $monthStart = now()->startOfYear()->month($month)->startOfMonth();
                $monthEnd = $monthStart->copy()->endOfMonth();

                $labels[] = $monthStart->format('M Y');
                $values[] = (int) DB::table('application')
                    ->whereBetween('submitted_at', [$monthStart, $monthEnd])
                    ->count();
            }

            return [
                'labels' => $labels,
                'values' => $values,
                'period' => $period,
            ];
        }

        if ($period === 'month') {
            $monthStart = now()->startOfMonth();
            $monthEnd = now()->endOfMonth();

            $rows = DB::table('application')
                ->selectRaw('DATE(submitted_at) as day, COUNT(*) as count')
                ->whereBetween('submitted_at', [$monthStart, $monthEnd])
                ->groupByRaw('DATE(submitted_at)')
                ->orderByRaw('DATE(submitted_at)')
                ->get();

            $countsByDay = [];
            foreach ($rows as $row) {
                $countsByDay[(string) $row->day] = (int) $row->count;
            }

            $labels = [];
            $values = [];
            $cursor = $monthStart->copy();

            while ($cursor->lessThanOrEqualTo($monthEnd)) {
                $isoDate = $cursor->toDateString();
                $labels[] = $cursor->format('M d');
                $values[] = $countsByDay[$isoDate] ?? 0;
                $cursor->addDay();
            }

            return [
                'labels' => $labels,
                'values' => $values,
                'period' => $period,
            ];
        }

        $start = now()->startOfWeek()->startOfDay();
        $end = now()->endOfWeek()->endOfDay();

        $rows = DB::table('application')
            ->selectRaw('DATE(submitted_at) as day, COUNT(*) as count')
            ->whereBetween('submitted_at', [$start, $end])
            ->groupByRaw('DATE(submitted_at)')
            ->orderByRaw('DATE(submitted_at)')
            ->get();

        $countsByDay = [];
        foreach ($rows as $row) {
            $countsByDay[(string) $row->day] = (int) $row->count;
        }

        $labels = [];
        $values = [];

        $cursor = $start->copy();

        while ($cursor->lessThanOrEqualTo($end)) {
            $isoDate = $cursor->toDateString();
            $labels[] = $cursor->format('D');
            $values[] = $countsByDay[$isoDate] ?? 0;
            $cursor->addDay();
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'period' => $period,
        ];
    }

    /**
     * @return list<array{application_id:int,reference_code:string,applicant_name:string,submitted_at:string,age_hours:int,status:string,status_label:string}>
     */
    public function getPendingReviewList(string $queue = 'new', int $limit = 5): array
    {
        if (! Schema::hasTable('application')) {
            return [];
        }

        $query = DB::table('application')
            ->select([
                'application_id',
                'reference_code',
                'applicant_last_name',
                'applicant_first_name',
                'status',
                'submitted_at',
            ])
            ->whereIn('status', ApplicationStatuses::pendingReview());

        if ($queue === 'old') {
            $query->whereDate('submitted_at', '<', now()->toDateString())
                ->orderBy('submitted_at');
        } else {
            $query->whereDate('submitted_at', now()->toDateString())
                ->orderByDesc('submitted_at');
        }

        return $query
            ->limit($limit)
            ->get()
            ->map(function (object $row): array {
                $submittedAt = $row->submitted_at !== null
                    ? now()->parse((string) $row->submitted_at)
                    : now();

                return [
                    'application_id' => (int) $row->application_id,
                    'reference_code' => (string) $row->reference_code,
                    'applicant_name' => trim(((string) $row->applicant_last_name) . ', ' . ((string) $row->applicant_first_name)),
                    'submitted_at' => $submittedAt->format('M d, Y h:i A'),
                    'age_hours' => max($submittedAt->diffInHours(now()), 0),
                    'status' => (string) $row->status,
                    'status_label' => ApplicationStatuses::label((string) $row->status),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return list<array{application_id:int,reference_code:string,applicant_name:string,submitted_at:string,age_hours:int,status:string,status_label:string}>
     */
    public function getOldestPending(int $limit = 5): array
    {
        return Cache::remember('aics_staff:analytics:oldest_pending:' . $limit, now()->addMinute(), function () use ($limit): array {
            if (! Schema::hasTable('application')) {
                return [];
            }

            $rows = DB::table('application')
                ->select([
                    'application_id',
                    'reference_code',
                    'applicant_last_name',
                    'applicant_first_name',
                    'status',
                    'submitted_at',
                ])
                ->whereIn('status', ApplicationStatuses::pendingReview())
                ->orderBy('submitted_at')
                ->limit($limit)
                ->get();

            return $rows
                ->map(function (object $row): array {
                    $submittedAt = $row->submitted_at !== null
                        ? now()->parse((string) $row->submitted_at)
                        : now();

                    return [
                        'application_id' => (int) $row->application_id,
                        'reference_code' => (string) $row->reference_code,
                        'applicant_name' => trim(((string) $row->applicant_last_name) . ', ' . ((string) $row->applicant_first_name)),
                        'submitted_at' => $submittedAt->format('M d, Y h:i A'),
                        'age_hours' => max($submittedAt->diffInHours(now()), 0),
                        'status' => (string) $row->status,
                        'status_label' => ApplicationStatuses::label((string) $row->status),
                    ];
                })
                ->values()
                ->all();
        });
    }

    /**
     * @return array{labels:list<string>,values:list<int>}
     */
    public function getSevenDaySubmissionTrend(): array
    {
        return Cache::remember('aics_staff:analytics:seven_day_submissions', now()->addMinute(), function (): array {
            if (! Schema::hasTable('application')) {
                return [
                    'labels' => [],
                    'values' => [],
                ];
            }

            $start = now()->startOfDay()->subDays(6);
            $end = now()->endOfDay();

            $rows = DB::table('application')
                ->selectRaw('DATE(submitted_at) as day, COUNT(*) as count')
                ->whereBetween('submitted_at', [$start, $end])
                ->groupByRaw('DATE(submitted_at)')
                ->orderByRaw('DATE(submitted_at)')
                ->get();

            $countsByDay = [];

            foreach ($rows as $row) {
                $countsByDay[(string) $row->day] = (int) $row->count;
            }

            $labels = [];
            $values = [];

            for ($offset = 6; $offset >= 0; $offset--) {
                $day = now()->startOfDay()->subDays($offset);
                $isoDate = $day->toDateString();

                $labels[] = $day->format('M d');
                $values[] = $countsByDay[$isoDate] ?? 0;
            }

            return [
                'labels' => $labels,
                'values' => $values,
            ];
        });
    }

    /**
     * @return array{labels:list<string>,values:list<int>}
     */
    public function getSevenDayApplicationsTrend(string $bucket = 'pending'): array
    {
        $trend = $this->getSevenDayApplicationsTrendMulti([$bucket]);

        return [
            'labels' => $trend['labels'],
            'values' => $trend['datasets'][0]['values'] ?? [],
        ];
    }

    /**
     * @param  array<int,string>  $buckets
     * @return array{labels:list<string>,datasets:list<array{bucket:string,label:string,color:string,values:list<int>}>}
     */
    public function getSevenDayApplicationsTrendMulti(array $buckets = ['pending']): array
    {
        if (! Schema::hasTable('application')) {
            return [
                'labels' => [],
                'datasets' => [],
            ];
        }

        $start = now()->startOfDay()->subDays(6);
        $end = now()->endOfDay();

        $normalizedBuckets = $this->normalizeBuckets($buckets);
        $labels = [];
        $isoDates = [];

        for ($offset = 6; $offset >= 0; $offset--) {
            $day = now()->startOfDay()->subDays($offset);
            $isoDates[] = $day->toDateString();
            $labels[] = $day->format('M d');
        }

        $datasets = [];

        foreach ($normalizedBuckets as $bucket) {
            $statuses = $this->getTrendStatuses($bucket);

            $rows = DB::table('application')
                ->selectRaw('DATE(submitted_at) as day, COUNT(*) as count')
                ->whereBetween('submitted_at', [$start, $end])
                ->whereIn('status', $statuses)
                ->groupByRaw('DATE(submitted_at)')
                ->orderByRaw('DATE(submitted_at)')
                ->get();

            $countsByDay = [];

            foreach ($rows as $row) {
                $countsByDay[(string) $row->day] = (int) $row->count;
            }

            $datasets[] = [
                'bucket' => $bucket,
                'label' => $this->trendBucketLabel($bucket),
                'color' => $this->trendBucketColor($bucket),
                'values' => collect($isoDates)
                    ->map(static fn (string $isoDate): int => $countsByDay[$isoDate] ?? 0)
                    ->values()
                    ->all(),
            ];
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }

    /**
     * @return array{labels:list<string>,values:list<int>}
     */
    public function getApplicationsTrendByDate(string $bucket, ?string $dateFrom, ?string $dateTo): array
    {
        $trend = $this->getApplicationsTrendByDateMulti([$bucket], $dateFrom, $dateTo);

        return [
            'labels' => $trend['labels'],
            'values' => $trend['datasets'][0]['values'] ?? [],
        ];
    }

    /**
     * @param  array<int,string>  $buckets
     * @return array{labels:list<string>,datasets:list<array{bucket:string,label:string,color:string,values:list<int>}>}
     */
    public function getApplicationsTrendByDateMulti(array $buckets, ?string $dateFrom, ?string $dateTo): array
    {
        if (! Schema::hasTable('application')) {
            return [
                'labels' => [],
                'datasets' => [],
            ];
        }

        $from = $dateFrom !== null && $dateFrom !== ''
            ? now()->parse($dateFrom)->startOfDay()
            : now()->startOfDay()->subDays(29);

        $to = $dateTo !== null && $dateTo !== ''
            ? now()->parse($dateTo)->endOfDay()
            : now()->endOfDay();

        if ($to->lessThan($from)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        $normalizedBuckets = $this->normalizeBuckets($buckets);

        $labels = [];
        $isoDates = [];

        $cursor = $from->copy()->startOfDay();
        $end = $to->copy()->startOfDay();

        while ($cursor->lessThanOrEqualTo($end)) {
            $isoDate = $cursor->toDateString();
            $isoDates[] = $isoDate;
            $labels[] = $cursor->format('M d');
            $cursor->addDay();
        }

        $datasets = [];

        foreach ($normalizedBuckets as $bucket) {
            $rows = DB::table('application')
                ->selectRaw('DATE(submitted_at) as day, COUNT(*) as count')
                ->whereBetween('submitted_at', [$from, $to])
                ->whereIn('status', $this->getTrendStatuses($bucket))
                ->groupByRaw('DATE(submitted_at)')
                ->orderByRaw('DATE(submitted_at)')
                ->get();

            $countsByDay = [];

            foreach ($rows as $row) {
                $countsByDay[(string) $row->day] = (int) $row->count;
            }

            $datasets[] = [
                'bucket' => $bucket,
                'label' => $this->trendBucketLabel($bucket),
                'color' => $this->trendBucketColor($bucket),
                'values' => collect($isoDates)
                    ->map(static fn (string $isoDate): int => $countsByDay[$isoDate] ?? 0)
                    ->values()
                    ->all(),
            ];
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }

    public function getAssistancesServedCount(?string $dateFrom, ?string $dateTo): int
    {
        if (! Schema::hasTable('application')) {
            return 0;
        }

        $query = DB::table('application')
            ->whereIn('status', ApplicationStatuses::assistancesServedStatuses());

        if (($dateFrom !== null && $dateFrom !== '') || ($dateTo !== null && $dateTo !== '')) {
            $from = $dateFrom !== null && $dateFrom !== ''
                ? now()->parse($dateFrom)->startOfDay()
                : now()->startOfDay()->subYears(5);
            $to = $dateTo !== null && $dateTo !== ''
                ? now()->parse($dateTo)->endOfDay()
                : now()->endOfDay();

            if ($to->lessThan($from)) {
                [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
            }

            $query->whereBetween('updated_at', [$from, $to]);
        }

        return (int) $query->count();
    }

    /**
     * @return list<array{assistance:string,count:int}>
     */
    public function getAssistanceAvailedFrequency(?string $dateFrom, ?string $dateTo): array
    {
        if (! Schema::hasTable('application')) {
            return [];
        }

        $hasCategoryTable = Schema::hasTable('assistance_category');

        $query = DB::table('application as a')
            ->selectRaw($hasCategoryTable
                ? 'COALESCE(c.name, "Uncategorized") as assistance, COUNT(*) as count'
                : 'COALESCE(CAST(a.category_id as CHAR), "Uncategorized") as assistance, COUNT(*) as count');

        if ($hasCategoryTable) {
            $query->leftJoin('assistance_category as c', 'c.category_id', '=', 'a.category_id');
        }

        if (($dateFrom !== null && $dateFrom !== '') || ($dateTo !== null && $dateTo !== '')) {
            $from = $dateFrom !== null && $dateFrom !== ''
                ? now()->parse($dateFrom)->startOfDay()
                : now()->startOfDay()->subYears(5);
            $to = $dateTo !== null && $dateTo !== ''
                ? now()->parse($dateTo)->endOfDay()
                : now()->endOfDay();

            if ($to->lessThan($from)) {
                [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
            }

            $query->whereBetween('a.submitted_at', [$from, $to]);
        }

        $rows = $query
            ->groupByRaw($hasCategoryTable ? 'c.name' : 'a.category_id')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return $rows
            ->map(static fn (object $row): array => [
                'assistance' => (string) $row->assistance,
                'count' => (int) $row->count,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array{total:int,sex:array<string,int>,age_groups:list<array{label:string,count:int}>,top_barangays:list<array{barangay:string,count:int}>}
     */
    public function getBeneficiaryDemographics(?string $dateFrom, ?string $dateTo, string $ageMin = '', string $ageMax = '', string $sex = 'all', string $barangay = ''): array
    {
        if (! Schema::hasTable('application')) {
            return [
                'total' => 0,
                'sex' => ['male' => 0, 'female' => 0],
                'age_groups' => [],
                'top_barangays' => [],
            ];
        }

        $base = DB::table('application');

        if (($dateFrom !== null && $dateFrom !== '') || ($dateTo !== null && $dateTo !== '')) {
            $from = $dateFrom !== null && $dateFrom !== ''
                ? now()->parse($dateFrom)->startOfDay()
                : now()->startOfDay()->subYears(5);
            $to = $dateTo !== null && $dateTo !== ''
                ? now()->parse($dateTo)->endOfDay()
                : now()->endOfDay();

            if ($to->lessThan($from)) {
                [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
            }

            $base->whereBetween('submitted_at', [$from, $to]);
        }

        if ($sex === 'male' || $sex === 'female') {
            $base->where('beneficiary_sex', $sex);
        }

        if ($barangay !== '') {
            $base->where('beneficiary_address', 'like', '%' . trim($barangay) . '%');
        }

        if ($ageMin !== '') {
            $base->whereRaw($this->ageYearsSql() . ' >= ?', [(int) $ageMin]);
        }

        if ($ageMax !== '') {
            $base->whereRaw($this->ageYearsSql() . ' <= ?', [(int) $ageMax]);
        }

        $sexRows = (clone $base)
            ->select('beneficiary_sex', DB::raw('COUNT(*) as count'))
            ->groupBy('beneficiary_sex')
            ->get();

        $sexCounts = [
            'male' => 0,
            'female' => 0,
        ];

        foreach ($sexRows as $row) {
            $key = strtolower((string) $row->beneficiary_sex);
            if (array_key_exists($key, $sexCounts)) {
                $sexCounts[$key] = (int) $row->count;
            }
        }

        $ageGroups = [
            ['label' => '0-17', 'range' => [0, 17]],
            ['label' => '18-30', 'range' => [18, 30]],
            ['label' => '31-45', 'range' => [31, 45]],
            ['label' => '46-60', 'range' => [46, 60]],
            ['label' => '61+', 'range' => [61, 200]],
        ];

        $ageGroupCounts = [];

        foreach ($ageGroups as $group) {
            $ageGroupCounts[] = [
                'label' => $group['label'],
                'count' => (int) (clone $base)
                    ->whereRaw($this->ageYearsSql() . ' BETWEEN ? AND ?', [$group['range'][0], $group['range'][1]])
                    ->count(),
            ];
        }

        $barangayExpression = $this->barangaySqlExpression();

        $barangayRows = (clone $base)
            ->selectRaw($barangayExpression . ' as barangay')
            ->selectRaw('COUNT(*) as count')
            ->groupByRaw($barangayExpression)
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return [
            'total' => (int) (clone $base)->count(),
            'sex' => $sexCounts,
            'age_groups' => $ageGroupCounts,
            'top_barangays' => $barangayRows
                ->map(static fn (object $row): array => [
                    'barangay' => (string) $row->barangay,
                    'count' => (int) $row->count,
                ])
                ->values()
                ->all(),
        ];
    }

    private function ageYearsSql(): string
    {
        $driver = FacadesDB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            return 'CAST((julianday("now") - julianday(beneficiary_dob)) / 365.25 AS INTEGER)';
        }

        return 'TIMESTAMPDIFF(YEAR, beneficiary_dob, CURDATE())';
    }

    private function barangaySqlExpression(): string
    {
        $driver = FacadesDB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            return "TRIM(CASE WHEN INSTR(beneficiary_address, ',') > 0 THEN SUBSTR(beneficiary_address, 1, INSTR(beneficiary_address, ',') - 1) ELSE beneficiary_address END)";
        }

        return "TRIM(SUBSTRING_INDEX(beneficiary_address, ',', 1))";
    }

    /**
     * @return list<string>
     */
    public function getTrendStatuses(string $bucket): array
    {
        return match ($bucket) {
            'forwarded' => ApplicationStatuses::forwardedStatuses(),
            'returned' => ApplicationStatuses::returnedStatuses(),
            default => ApplicationStatuses::pendingStatuses(),
        };
    }

    /**
     * @param  array<int,string>  $buckets
     * @return list<string>
     */
    private function normalizeBuckets(array $buckets): array
    {
        $allowed = ['pending', 'forwarded', 'returned'];

        $normalized = collect($buckets)
            ->map(static fn (mixed $bucket): string => (string) $bucket)
            ->filter(static fn (string $bucket): bool => in_array($bucket, $allowed, true))
            ->unique()
            ->values()
            ->all();

        return $normalized === [] ? ['pending'] : $normalized;
    }

    private function trendBucketLabel(string $bucket): string
    {
        return match ($bucket) {
            'forwarded' => 'Forwarded',
            'returned' => 'Returned',
            default => 'Pending',
        };
    }

    private function trendBucketColor(string $bucket): string
    {
        return match ($bucket) {
            'forwarded' => '#176334',
            'returned' => '#B45309',
            default => '#0F766E',
        };
    }

    /**
     * @return list<array{status:string,status_label:string,count:int}>
     */
    public function getStatusBreakdown(): array
    {
        return Cache::remember('aics_staff:analytics:status_breakdown', now()->addMinute(), function (): array {
            if (! Schema::hasTable('application')) {
                return [];
            }

            $rows = DB::table('application')
                ->select('status', DB::raw('COUNT(*) as count'))
                ->whereIn('status', ApplicationStatuses::all())
                ->groupBy('status')
                ->orderByDesc('count')
                ->get();

            return $rows
                ->map(static fn (object $row): array => [
                    'status' => (string) $row->status,
                    'status_label' => ApplicationStatuses::label((string) $row->status),
                    'count' => (int) $row->count,
                ])
                ->values()
                ->all();
        });
    }
}
