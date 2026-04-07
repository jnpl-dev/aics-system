<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-4">
            <div>
                <h3 class="text-base font-semibold text-gray-900">7 Day Applications Trend</h3>
                <p class="text-xs text-gray-500">Applications received from Monday to Sunday.</p>
            </div>

            @php
                $trend = $this->getTrendData();
                $labels = $trend['labels'];
                $values = $trend['values'];
                $maxValue = max(array_merge([1], $values));

                $svgWidth = 960;
                $svgHeight = 290;
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

            <div class="rounded-lg border border-gray-100 bg-white p-3">
                <svg viewBox="0 0 {{ $svgWidth }} {{ $svgHeight }}" class="h-80 w-full">
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

        </div>
    </x-filament::section>
</x-filament-widgets::widget>
