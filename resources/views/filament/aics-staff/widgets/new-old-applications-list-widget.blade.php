<x-filament-widgets::widget>
    <x-filament::section>
        <div class="grid gap-6 xl:grid-cols-2">
            <section>
                <h3 class="mb-3 text-base font-semibold text-gray-900">New Applications List</h3>
                <div class="space-y-2">
                    @forelse ($this->getNewApplications() as $row)
                        <article class="rounded-lg border border-gray-100 p-3 text-sm">
                            <div class="flex items-center justify-between">
                                <strong class="text-gray-900">{{ $row['reference_code'] }}</strong>
                                <a href="{{ \App\Filament\Resources\Applications\ApplicationResource::getUrl('review', ['record' => $row['application_id']]) }}" class="text-xs font-semibold text-emerald-700 hover:underline">Review</a>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">{{ $row['applicant_name'] }} · {{ $row['status_label'] }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ $row['submitted_at'] }}</p>
                        </article>
                    @empty
                        <p class="text-sm text-gray-500">No new applications.</p>
                    @endforelse
                </div>
            </section>

            <section>
                <h3 class="mb-3 text-base font-semibold text-gray-900">Old Applications List</h3>
                <div class="space-y-2">
                    @forelse ($this->getOldApplications() as $row)
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
    </x-filament::section>
</x-filament-widgets::widget>
