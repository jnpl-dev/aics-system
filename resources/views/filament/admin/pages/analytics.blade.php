<x-filament-panels::page>
    @php
        $severityToneClasses = [
            'warning' => 'bg-yellow-100 text-yellow-700 ring-yellow-600/20',
            'high' => 'bg-orange-100 text-orange-700 ring-orange-600/20',
            'critical' => 'bg-red-100 text-red-700 ring-red-600/20',
        ];

        $latestRowStart = ($latestActivities->currentPage() - 1) * $latestActivities->perPage() + 1;
        $unusualRowStart = ($unusualActivities->currentPage() - 1) * $unusualActivities->perPage() + 1;
    @endphp

    @php
        $latestActivities = $latestActivities ?? ($this->getLatestActivities() ?? []);
        $unusualActivities = $unusualActivities ?? ($this->getUnusualActivities() ?? []);
        $analyticsUrl = \App\Filament\Pages\Analytics::getUrl();

        $severityToneClasses = [
            'warning' => 'bg-yellow-100 text-yellow-700 ring-yellow-600/20',
            'high' => 'bg-orange-100 text-orange-700 ring-orange-600/20',
            'critical' => 'bg-red-100 text-red-700 ring-red-600/20',
        ];
    @endphp

    @php
        $kpis = $kpis ?? ($this->getKpis() ?? ['active_users' => 0, 'inactive_users' => 0, 'total_users' => 0]);
    @endphp

    <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <h2 class="mb-4 text-base font-semibold text-gray-900">User Analytics Summary</h2>

        <div class="grid gap-3 sm:grid-cols-3">
            <article class="flex min-h-28 flex-col justify-between rounded-lg bg-emerald-500 p-3 text-white shadow-sm">
                <p class="text-lg text-white/90">Active Users</p>
                <p class="text-4xl font-semibold leading-none">{{ number_format($kpis['active_users']) }}</p>
            </article>

            <article class="flex min-h-28 flex-col justify-between rounded-lg bg-emerald-600 p-3 text-white shadow-sm">
                <p class="text-lg text-white/90">Inactive Users</p>
                <p class="text-4xl font-semibold leading-none">{{ number_format($kpis['inactive_users']) }}</p>
            </article>

            <article class="flex min-h-28 flex-col justify-between rounded-lg bg-emerald-700 p-3 text-white shadow-sm">
                <p class="text-lg text-white/90">Total Users</p>
                <p class="text-4xl font-semibold leading-none">{{ number_format($kpis['total_users']) }}</p>
            </article>
        </div>
    </section>

    <div class="grid gap-6">
        <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="mb-3">
                <h2 class="text-base font-semibold text-gray-900">Latest Activities</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-600">
                        <tr>
                            <th scope="col" class="px-3 py-2 text-left">User ID</th>
                            <th scope="col" class="px-3 py-2 text-left">User</th>
                            <th scope="col" class="px-3 py-2 text-left">Action</th>
                            <th scope="col" class="px-3 py-2 text-left">Module/Page</th>
                            <th scope="col" class="px-3 py-2 text-left">IP Address</th>
                            <th scope="col" class="px-3 py-2 text-left">Date &amp; Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white text-gray-700">
                        @forelse ($latestActivities as $row)
                            <tr>
                                <td class="px-3 py-2 align-top">{{ $row['user_id'] }}</td>
                                <td class="px-3 py-2 align-top">{{ $row['user'] ?? '' }}</td>
                                <td class="px-3 py-2 align-top">{{ $row['action'] }}</td>
                                <td class="px-3 py-2 align-top">{{ $row['module_page'] }}</td>
                                <td class="px-3 py-2 align-top">{{ $row['ip_address'] }}</td>
                                <td class="px-3 py-2 align-top">{{ $row['date_time'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-6 text-center text-sm text-gray-500">No activity records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="mb-3">
                <h2 class="text-base font-semibold text-gray-900">Unusual Activities</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-600">
                        <tr>
                            <th scope="col" class="px-3 py-2 text-left">User ID</th>
                            <th scope="col" class="px-3 py-2 text-left">User</th>
                            <th scope="col" class="px-3 py-2 text-left">Flagged Reason</th>
                            <th scope="col" class="px-3 py-2 text-left">Attempt Count</th>
                            <th scope="col" class="px-3 py-2 text-left">Last Attempt</th>
                            <th scope="col" class="px-3 py-2 text-left">Severity</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white text-gray-700">
                        @forelse ($unusualActivities as $row)
                            <tr>
                                <td class="px-3 py-2 align-top">{{ $row['user_id'] }}</td>
                                <td class="px-3 py-2 align-top">{{ $row['user'] ?? '' }}</td>
                                <td class="px-3 py-2 align-top">{{ $row['flagged_reason'] }}</td>
                                <td class="px-3 py-2 align-top">{{ $row['attempt_count'] }}</td>
                                <td class="px-3 py-2 align-top">{{ $row['last_attempt'] }}</td>
                                <td class="px-3 py-2 align-top">
                                    @php
                                        $badgeClass = $severityToneClasses[$row['severity_tone']] ?? 'bg-gray-100 text-gray-700 ring-gray-500/10';
                                    @endphp
                                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-semibold ring-1 ring-inset {{ $badgeClass }}">
                                        {{ $row['severity'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-6 text-center text-sm text-gray-500">No unusual activity patterns detected.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-filament-panels::page>
