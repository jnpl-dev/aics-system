<x-filament-widgets::widget>
    @php
        $kpis = $this->getKpis();
    @endphp

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="rounded-xl border border-gray-200 bg-white p-4">
            <h3 class="mb-3 text-base font-semibold text-gray-900">Applications</h3>
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

        <section class="rounded-xl border border-gray-200 bg-white p-4">
            <h3 class="mb-3 text-base font-semibold text-gray-900">Assistance Code</h3>
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
</x-filament-widgets::widget>
