<x-filament-widgets::widget>
    @php
        $kpis = $this->getKpis();
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
</x-filament-widgets::widget>
