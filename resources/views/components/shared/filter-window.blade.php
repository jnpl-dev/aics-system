@props([
    'roleOptions' => [],
    'statusOptions' => [],
    'selectedRole' => '',
    'selectedStatus' => '',
    'resetUrl' => '#',
])

<details class="group relative">
    <x-shared.filter-button id="filter-window-trigger" label="Filters" />

    <div class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/40 p-4 group-open:flex">
        <button
            type="button"
            aria-label="Close filter window"
            class="absolute inset-0 h-full w-full cursor-default"
            onclick="this.closest('details')?.removeAttribute('open')"
        ></button>

        <div class="relative z-[101] w-full max-w-md rounded-xl border border-[#1F6336]/20 bg-white p-5 shadow-2xl">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-base font-semibold text-[#1F6336]">Filter users</h3>
                <button
                    type="button"
                    aria-label="Close filter window"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-[#1F6336]/20 text-[#1F6336] transition hover:bg-[#F0F3EF]"
                    onclick="this.closest('details')?.removeAttribute('open')"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="space-y-4">
                <div>
                    <label for="filter-role" class="mb-1 block text-xs font-semibold uppercase text-[#1F6336]">Role</label>
                    <select
                        id="filter-role"
                        name="user_role"
                        class="w-full rounded-md border border-[#1F6336]/20 bg-white px-3 py-2 text-sm text-gray-700 outline-none transition focus:border-[#3DA814] focus:ring-2 focus:ring-[#3DA814]/20"
                    >
                        <option value="">All roles</option>
                        @foreach ($roleOptions as $value => $label)
                            <option value="{{ $value }}" @selected((string) $selectedRole === (string) $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="filter-status" class="mb-1 block text-xs font-semibold uppercase text-[#1F6336]">Status</label>
                    <select
                        id="filter-status"
                        name="user_status"
                        class="w-full rounded-md border border-[#1F6336]/20 bg-white px-3 py-2 text-sm text-gray-700 outline-none transition focus:border-[#3DA814] focus:ring-2 focus:ring-[#3DA814]/20"
                    >
                        <option value="">All statuses</option>
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected((string) $selectedStatus === (string) $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center justify-end gap-2 pt-1">
                    <a
                        href="{{ $resetUrl }}"
                        data-dashboard-pagination
                        class="rounded-md border border-[#1F6336]/20 px-3 py-2 text-xs font-semibold uppercase text-[#1F6336] transition hover:bg-[#F0F3EF]"
                    >
                        Reset
                    </a>
                    <button
                        type="submit"
                        class="rounded-md bg-[#1F6336] px-3 py-2 text-xs font-semibold uppercase text-white transition hover:bg-[#184D2A]"
                    >
                        Apply
                    </button>
                </div>
            </div>
        </div>
    </div>
</details>