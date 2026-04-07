<x-filament-panels::page>
    <div class="grid gap-6 xl:grid-cols-2">
        <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="mb-4 text-base font-semibold text-gray-900">Applications</h2>
            <div class="grid gap-3 sm:grid-cols-3">
                <article class="flex min-h-28 flex-col justify-between rounded-lg bg-emerald-500 p-3 text-white shadow-sm">
                    <p class="text-xs text-white/90">Submitted (Pending Applications)</p>
                    <p class="text-4xl font-semibold leading-none">{{ number_format($kpis['submitted']) }}</p>
                </article>
                <article class="flex min-h-28 flex-col justify-between rounded-lg bg-emerald-600 p-3 text-white shadow-sm">
                    <p class="text-xs text-white/90">Forwarded (Forwarded to MSWDO)</p>
                    <p class="text-4xl font-semibold leading-none">{{ number_format($kpis['forwarded']) }}</p>
                </article>
                <article class="flex min-h-28 flex-col justify-between rounded-lg bg-emerald-700 p-3 text-white shadow-sm">
                    <p class="text-xs text-white/90">Returned (Resubmission Required)</p>
                    <p class="text-4xl font-semibold leading-none">{{ number_format($kpis['returned']) }}</p>
                </article>
            </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="mb-4 text-base font-semibold text-gray-900">Assistance Code</h2>
            <div class="grid gap-3 sm:grid-cols-3">
                <article class="flex min-h-28 flex-col justify-between rounded-lg bg-emerald-500 p-3 text-white shadow-sm">
                    <p class="text-xs text-white/90">Pending (Pending Assistance Code)</p>
                    <p class="text-4xl font-semibold leading-none">{{ number_format($kpis['pending_code']) }}</p>
                </article>
                <article class="flex min-h-28 flex-col justify-between rounded-lg bg-emerald-600 p-3 text-white shadow-sm">
                    <p class="text-xs text-white/90">Forwarded (Forwarded to Mayor's Office)</p>
                    <p class="text-4xl font-semibold leading-none">{{ number_format($kpis['forwarded_code']) }}</p>
                </article>
                <article class="flex min-h-28 flex-col justify-between rounded-lg bg-emerald-700 p-3 text-white shadow-sm">
                    <p class="text-xs text-white/90">Returned (Code Adjustment Required)</p>
                    <p class="text-4xl font-semibold leading-none">{{ number_format($kpis['returned_code']) }}</p>
                </article>
            </div>
        </section>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="mb-3 text-base font-semibold text-gray-900">New Applications List</h2>
            <div class="space-y-2">
                @forelse ($newPendingList as $row)
                    <article class="rounded-lg border border-gray-100 p-3 text-sm">
                        <div class="flex items-center justify-between">
                            <strong class="text-gray-900">{{ $row['reference_code'] }}</strong>
                            <a href="{{ \App\Filament\Resources\Applications\ApplicationResource::getUrl('review', ['record' => $row['application_id']]) }}" class="text-xs font-semibold text-emerald-700 hover:underline">Review</a>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">{{ $row['applicant_name'] }} · {{ $row['status_label'] }}</p>
                        <p class="mt-1 text-xs text-gray-500">Submitted: {{ $row['submitted_at'] }}</p>
                    </article>
                @empty
                    <p class="text-sm text-gray-500">No new applications.</p>
                @endforelse
            </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="mb-3 text-base font-semibold text-gray-900">Old Applications List</h2>
            <div class="space-y-2">
                @forelse ($oldPendingList as $row)
                    <article class="rounded-lg border border-gray-100 p-3 text-sm">
                        <div class="flex items-center justify-between">
                            <strong class="text-gray-900">{{ $row['reference_code'] }}</strong>
                            <a href="{{ \App\Filament\Resources\Applications\ApplicationResource::getUrl('review', ['record' => $row['application_id']]) }}" class="text-xs font-semibold text-emerald-700 hover:underline">Review</a>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">{{ $row['applicant_name'] }} · {{ $row['status_label'] }}</p>
                        <p class="mt-1 text-xs text-gray-500">{{ $row['age_hours'] }} hour(s) pending</p>
                    </article>
                @empty
                    <p class="text-sm text-gray-500">No old applications.</p>
                @endforelse
            </div>
        </section>
    </div>

    <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="mb-4 flex flex-wrap items-end justify-between gap-4">
            <h2 class="text-base font-semibold text-gray-900">Application Trend</h2>
            <div class="grid gap-3 sm:grid-cols-1 lg:grid-cols-1">
                <label class="block text-sm">
                    <select wire:model.live="trendPeriod" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                        <option value="week">Week</option>
                        <option value="month">Month</option>
                        <option value="year">Year</option>
                    </select>
                </label>
            </div>
        </div>

        @php
            $labels = $applicationsTrend['labels'];
            $values = $applicationsTrend['values'];
            $maxValue = max(array_merge([1], $values));

            $svgWidth = 1120;
            $svgHeight = 330;
            $paddingLeft = 40;
            $paddingRight = 12;
            $paddingTop = 16;
            $paddingBottom = 34;
            $plotWidth = $svgWidth - $paddingLeft - $paddingRight;
            $plotHeight = $svgHeight - $paddingTop - $paddingBottom;
            $pointCount = max(count($labels), 1);
            $stepX = $pointCount > 1 ? $plotWidth / ($pointCount - 1) : 0;

            $xFor = static fn (int $index): float => $paddingLeft + ($index * $stepX);
            $yFor = static fn (int $value): float => $paddingTop + ($plotHeight - (($value / $maxValue) * $plotHeight));

            $points = collect($values)
                ->map(static fn (int $value, int $index): string => number_format($xFor($index), 2, '.', '') . ',' . number_format($yFor($value), 2, '.', ''))
                ->implode(' ');
        @endphp

        <div class="mt-4 rounded-lg border border-gray-100 bg-white p-3">
            <svg viewBox="0 0 {{ $svgWidth }} {{ $svgHeight }}" class="h-[22rem] w-full">
                @for ($grid = 0; $grid <= 4; $grid++)
                    @php
                        $ratio = $grid / 4;
                        $y = $paddingTop + ($plotHeight * $ratio);
                        $tickValue = (int) round($maxValue - ($maxValue * $ratio));
                    @endphp
                    <line x1="{{ $paddingLeft }}" y1="{{ $y }}" x2="{{ $paddingLeft + $plotWidth }}" y2="{{ $y }}" stroke="#E5E7EB" stroke-width="1" />
                    <text x="6" y="{{ $y + 4 }}" fill="#6B7280" font-size="11">{{ $tickValue }}</text>
                @endfor

                <polyline points="{{ $points }}" fill="none" stroke="#176334" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />

                @foreach ($values as $index => $value)
                    <circle cx="{{ $xFor($index) }}" cy="{{ $yFor($value) }}" r="3.4" fill="#176334" />
                @endforeach

                @foreach ($labels as $index => $label)
                    <text x="{{ $xFor($index) }}" y="{{ $paddingTop + $plotHeight + 18 }}" text-anchor="middle" fill="#6B7280" font-size="11">{{ $label }}</text>
                @endforeach
            </svg>
        </div>
    </section>
</x-filament-panels::page>
