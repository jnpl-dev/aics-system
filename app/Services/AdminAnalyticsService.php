<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminAnalyticsService
{
    /**
     * @return array{active_users:int,inactive_users:int,total_users:int}
     */
    public function getKpis(): array
    {
        if (! Schema::hasTable('user')) {
            return [
                'active_users' => 0,
                'inactive_users' => 0,
                'total_users' => 0,
            ];
        }

        return [
            'active_users' => (int) DB::table('user')->where('status', 'active')->count(),
            'inactive_users' => (int) DB::table('user')->where('status', 'inactive')->count(),
            'total_users' => (int) DB::table('user')->count(),
        ];
    }

    /**
     * @return list<array{log_id:int,user_id:int,user:string,action:string,module_page:string,ip_address:string,date_time:string}>
     */
    public function getLatestActivities(int $limit = 5): array
    {
        if (! Schema::hasTable('audit_log')) {
            return [];
        }

        return DB::table('audit_log as logs')
            ->leftJoin('user as users', 'users.user_id', '=', 'logs.user_id')
            ->select([
                'logs.log_id',
                'logs.user_id',
                'logs.action',
                'logs.module',
                'logs.description',
                'logs.ip_address',
                'logs.timestamp',
                'users.first_name',
                'users.last_name',
                'users.email as user_email',
            ])
            ->orderByDesc('logs.timestamp')
            ->orderByDesc('logs.log_id')
            ->limit($limit)
            ->get()
        ->map(fn (object $row): array => $this->mapLatestActivityRow($row))
            ->values()
            ->all();
    }

    /**
     * @return LengthAwarePaginator<array{log_id:int,user_id:int,user:string,action:string,module_page:string,ip_address:string,date_time:string}>
     */
    public function paginateLatestActivities(int $perPage = 10, string $pageName = 'latestActivitiesPage'): LengthAwarePaginator
    {
        if (! Schema::hasTable('audit_log')) {
            return new Paginator([], 0, $perPage, 1, [
                'path' => request()->url(),
                'pageName' => $pageName,
            ]);
        }

        $paginator = DB::table('audit_log as logs')
            ->leftJoin('user as users', 'users.user_id', '=', 'logs.user_id')
            ->select([
                'logs.log_id',
                'logs.user_id',
                'logs.action',
                'logs.module',
                'logs.description',
                'logs.ip_address',
                'logs.timestamp',
                'users.first_name',
                'users.last_name',
                'users.email as user_email',
            ])
            ->orderByDesc('logs.timestamp')
            ->orderByDesc('logs.log_id')
            ->paginate($perPage, ['*'], $pageName);

        $paginator->setCollection(
            $paginator->getCollection()
                ->map(fn (object $row): array => $this->mapLatestActivityRow($row))
                ->values()
        );

        return $paginator;
    }

    /**
     * @return list<array{user_id:int,user:string,flagged_reason:string,attempt_count:int,last_attempt:string,last_attempt_at:string,severity:string,severity_tone:string}>
     */
    public function getUnusualActivities(int $limit = 5): array
    {
        return $this->buildUnusualActivitiesCollection()
            ->take($limit)
            ->values()
            ->all();
    }

    /**
     * @return LengthAwarePaginator<array{user_id:int,user:string,flagged_reason:string,attempt_count:int,last_attempt:string,last_attempt_at:string,severity:string,severity_tone:string}>
     */
    public function paginateUnusualActivities(int $perPage = 10, string $pageName = 'unusualActivitiesPage'): LengthAwarePaginator
    {
        return $this->paginateCollection(
            $this->buildUnusualActivitiesCollection(),
            $perPage,
            $pageName,
        );
    }

    /**
     * @param  object{log_id:int,user_id:int,action:string,module:string,description:?string,ip_address:?string,timestamp:mixed,first_name:?string,last_name:?string,user_email:?string}  $row
     * @return array{log_id:int,user_id:int,user:string,action:string,module_page:string,ip_address:string,date_time:string}
     */
    private function mapLatestActivityRow(object $row): array
    {
        return [
            'log_id' => (int) $row->log_id,
            'user_id' => (int) ($row->user_id ?? 0),
            'user' => $this->resolveUserDisplayName(
                (int) ($row->user_id ?? 0),
                is_string($row->first_name ?? null) ? (string) $row->first_name : '',
                is_string($row->last_name ?? null) ? (string) $row->last_name : '',
                is_string($row->user_email ?? null) ? (string) $row->user_email : '',
                $this->extractEmailFromDescription(is_string($row->description ?? null) ? (string) $row->description : ''),
            ),
            'action' => strtoupper((string) ($row->action ?? '')),
            'module_page' => (string) ($row->module ?? 'unknown'),
            'ip_address' => (string) ($row->ip_address ?? 'N/A'),
            'date_time' => $this->formatTimestamp($row->timestamp),
        ];
    }

    /**
     * @return Collection<int, array{user:string,flagged_reason:string,attempt_count:int,last_attempt:string,last_attempt_at:string,severity:string,severity_tone:string}>
     */
    private function buildUnusualActivitiesCollection(): Collection
    {
        if (! Schema::hasTable('audit_log')) {
            return collect();
        }

        $rows = DB::table('audit_log as logs')
            ->leftJoin('user as users', 'users.user_id', '=', 'logs.user_id')
            ->select([
                'logs.log_id',
                'logs.user_id',
                'logs.description',
                'logs.timestamp',
                'users.first_name',
                'users.last_name',
                'users.email as user_email',
            ])
            ->where('logs.module', 'authentication')
            ->where('logs.action', 'login')
            ->where(function ($query): void {
                $query
                    ->where('logs.description', 'like', '%event=AUTH_LOGIN_FAILED%')
                    ->orWhere('logs.description', 'like', '%event=AUTH_LOGIN_SUCCESS%');
            })
            ->orderBy('logs.timestamp')
            ->orderBy('logs.log_id')
            ->get();

        /** @var array<string, array{timestamps:list<Carbon>, latest?:array{user:string,flagged_reason:string,attempt_count:int,last_attempt:string,last_attempt_at:string,severity:string,severity_tone:string}}> $state */
        $state = [];

        foreach ($rows as $row) {
            $description = is_string($row->description ?? null) ? (string) $row->description : '';
            $event = $this->extractAuditEvent($description);
            $emailFromDescription = $this->extractEmailFromDescription($description);
            $userId = is_numeric($row->user_id ?? null) ? (int) $row->user_id : 0;
            $userEmail = is_string($row->user_email ?? null) ? (string) $row->user_email : '';
            $identityEmail = $emailFromDescription !== '' ? $emailFromDescription : $userEmail;

            $identityKey = $userId > 0
                ? "user:{$userId}"
                : ($identityEmail !== '' ? 'email:' . strtolower($identityEmail) : null);

            if ($identityKey === null) {
                continue;
            }

            if (! array_key_exists($identityKey, $state)) {
                $state[$identityKey] = ['timestamps' => []];
            }

            $timestamp = $this->toCarbon($row->timestamp);

            if ($event === 'AUTH_LOGIN_SUCCESS') {
                $state[$identityKey]['timestamps'] = [];

                continue;
            }

            if ($event !== 'AUTH_LOGIN_FAILED') {
                continue;
            }

            $state[$identityKey]['timestamps'][] = $timestamp;

            while (
                count($state[$identityKey]['timestamps']) > 0
                && $state[$identityKey]['timestamps'][0]->diffInMinutes($timestamp) > 60
            ) {
                array_shift($state[$identityKey]['timestamps']);
            }

            $attemptCount = count($state[$identityKey]['timestamps']);

            if ($attemptCount < 5) {
                continue;
            }

            [$severityLabel, $severityTone] = $this->mapSeverity($attemptCount);

            $state[$identityKey]['latest'] = [
                'user_id' => $userId,
                'user' => $this->resolveUserDisplayName(
                    $userId,
                    is_string($row->first_name ?? null) ? (string) $row->first_name : '',
                    is_string($row->last_name ?? null) ? (string) $row->last_name : '',
                    $userEmail,
                    $identityEmail,
                ),
                'flagged_reason' => '5+ consecutive failed login attempts within 1 hour',
                'attempt_count' => $attemptCount,
                'last_attempt' => $timestamp->format('M d, Y h:i A'),
                'last_attempt_at' => $timestamp->toIso8601String(),
                'severity' => $severityLabel,
                'severity_tone' => $severityTone,
            ];
        }

        return collect($state)
            ->pluck('latest')
            ->filter(static fn ($value): bool => is_array($value))
            ->values()
            ->sortByDesc(static fn (array $row): string => (string) $row['last_attempt_at'])
            ->values();
    }

    /**
     * @param  Collection<int, mixed>  $items
     * @return LengthAwarePaginator<mixed>
     */
    private function paginateCollection(Collection $items, int $perPage, string $pageName): LengthAwarePaginator
    {
        $page = Paginator::resolveCurrentPage($pageName);
        $total = $items->count();
        $results = $items->slice(($page - 1) * $perPage, $perPage)->values();

        return new Paginator(
            $results,
            $total,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'pageName' => $pageName,
            ]
        );
    }

    /**
     * @return array{0:string,1:string}
     */
    private function mapSeverity(int $attemptCount): array
    {
        if ($attemptCount >= 11) {
            return ['Critical', 'critical'];
        }

        if ($attemptCount >= 8) {
            return ['High', 'high'];
        }

        return ['Warning', 'warning'];
    }

    private function extractAuditEvent(string $description): string
    {
        if (preg_match('/(?:^|;\s*)event=([^;]+)/', $description, $matches) === 1) {
            return trim((string) ($matches[1] ?? ''));
        }

        return '';
    }

    private function extractEmailFromDescription(string $description): string
    {
        if (preg_match('/(?:^|;\s*)email=([^;]+)/', $description, $matches) === 1) {
            return trim((string) ($matches[1] ?? ''));
        }

        return '';
    }

    private function resolveUserDisplayName(
        int $userId,
        string $firstName,
        string $lastName,
        string $userEmail,
        string $fallbackEmail
    ): string {
        $fullName = trim($firstName . ' ' . $lastName);

        if ($fullName !== '') {
            return $fullName;
        }

        if ($userEmail !== '') {
            return $userEmail;
        }

        if ($fallbackEmail !== '') {
            return $fallbackEmail;
        }

        return $userId > 0 ? "User #{$userId}" : 'Unknown User';
    }

    private function formatTimestamp(mixed $value): string
    {
        return $this->toCarbon($value)->format('M d, Y h:i A');
    }

    private function toCarbon(mixed $value): Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        return Carbon::parse((string) $value);
    }
}
