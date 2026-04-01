@php
    $usersPaginator = $users ?? null;
    $isPaginator = $usersPaginator instanceof \Illuminate\Contracts\Pagination\Paginator;
    $rows = $isPaginator ? $usersPaginator->items() : (is_iterable($usersPaginator) ? $usersPaginator : []);
    $filters = is_array($userManagementFilters ?? null) ? $userManagementFilters : [];
    $searchValue = (string) ($filters['search'] ?? '');
    $roleValue = (string) ($filters['role'] ?? '');
    $statusValue = (string) ($filters['status'] ?? '');
    $basePaginationUrl = route('dashboard.content', ['tab' => 'user-management']);

    $roleOptions = [
        'admin' => 'Admin',
        'aics_staff' => 'AICS Staff',
        'mswd_officer' => 'MSWD Officer',
        'mayor_office_staff' => 'Mayor\'s Office Staff',
        'accountant' => 'Accountant',
        'treasurer' => 'Treasurer',
    ];

    $statusOptions = [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ];
@endphp

<div class="relative flex w-full flex-col rounded-xl border border-[#1F6336]/15 bg-white bg-clip-border text-gray-700 shadow-md">
    <div class="relative mx-4 mt-4 overflow-hidden rounded-none bg-white bg-clip-border text-gray-700">
        <div class="mb-6 flex items-start justify-between gap-4">
            <div>
                <h5 class="block text-xl font-semibold leading-snug tracking-normal text-[#1F6336]">User management</h5>
                <p class="mt-1 block text-sm font-normal leading-relaxed text-gray-600">
                    Manage account access, roles, and status for all system users.
                </p>
            </div>
            <span class="rounded-lg border border-[#1F6336]/20 px-4 py-2 text-center text-xs font-bold uppercase text-[#1F6336]">
                Total {{ $isPaginator ? $usersPaginator->total() : count($rows) }}
            </span>
        </div>

        <form
            method="GET"
            action="{{ $basePaginationUrl }}"
            data-dashboard-fragment-form
            class="rounded-lg border border-[#1F6336]/10 bg-[#F8FBF8] p-4"
        >
            <div class="flex flex-col gap-3 md:flex-row md:items-center">
                <div class="flex-1">
                <x-shared.search-bar
                    id="user-search"
                    name="user_search"
                    :value="$searchValue"
                    placeholder="Name or email"
                    button-text="Search"
                />
                </div>

                <div class="flex items-center gap-2">
                    <x-shared.filter-window
                        :role-options="$roleOptions"
                        :status-options="$statusOptions"
                        :selected-role="$roleValue"
                        :selected-status="$statusValue"
                        :reset-url="$basePaginationUrl"
                    />

                    <x-shared.add-user-window :action="route('admin.users.store')" />
                </div>
            </div>

        </form>
    </div>

    <div class="max-h-[34rem] overflow-auto px-6 pb-0">
        <table class="mt-4 w-full min-w-max table-auto text-left">
            <thead class="sticky top-0 z-10">
                <tr>
                    <th class="border-y border-[#1F6336]/10 bg-[#F0F3EF] p-4">
                        <p class="text-xs font-semibold uppercase leading-none text-gray-600">ID</p>
                    </th>
                    <th class="border-y border-[#1F6336]/10 bg-[#F0F3EF] p-4">
                        <p class="text-xs font-semibold uppercase leading-none text-gray-600">Name</p>
                    </th>
                    <th class="border-y border-[#1F6336]/10 bg-[#F0F3EF] p-4">
                        <p class="text-xs font-semibold uppercase leading-none text-gray-600">Email</p>
                    </th>
                    <th class="border-y border-[#1F6336]/10 bg-[#F0F3EF] p-4">
                        <p class="text-xs font-semibold uppercase leading-none text-gray-600">Role</p>
                    </th>
                    <th class="border-y border-[#1F6336]/10 bg-[#F0F3EF] p-4">
                        <p class="text-xs font-semibold uppercase leading-none text-gray-600">Status</p>
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $user)
                    <tr>
                        <td class="border-b border-[#1F6336]/10 p-4 text-sm text-gray-700">#{{ $user->user_id }}</td>
                        <td class="border-b border-[#1F6336]/10 p-4 text-sm font-medium text-[#1F6336]">
                            {{ trim(($user->first_name ?? '').' '.($user->last_name ?? '')) !== '' ? trim(($user->first_name ?? '').' '.($user->last_name ?? '')) : '-' }}
                        </td>
                        <td class="border-b border-[#1F6336]/10 p-4 text-sm text-gray-700">{{ $user->email ?? '-' }}</td>
                        <td class="border-b border-[#1F6336]/10 p-4 text-sm text-gray-700">{{ str_replace('_', ' ', (string) ($user->role ?? '-')) }}</td>
                        <td class="border-b border-[#1F6336]/10 p-4">
                            @php
                                $status = strtolower((string) ($user->status ?? 'unknown'));
                                $statusClass = match ($status) {
                                    'active' => 'bg-emerald-100 text-emerald-700',
                                    'inactive' => 'bg-gray-100 text-gray-700',
                                    'suspended' => 'bg-red-100 text-red-700',
                                    default => 'bg-yellow-100 text-yellow-700',
                                };
                            @endphp
                            <span class="inline-flex rounded-md px-2 py-1 text-xs font-semibold uppercase {{ $statusClass }}">
                                {{ $status }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-6 text-center text-sm text-gray-500">No users found for the selected filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($isPaginator)
        <div class="flex items-center justify-between border-t border-[#1F6336]/10 p-4">
            <p class="text-sm font-normal text-gray-700">
                Page {{ $usersPaginator->currentPage() }} of {{ $usersPaginator->lastPage() }}
            </p>
            <div class="flex gap-2">
                @php
                    $queryString = http_build_query(array_filter([
                        'user_search' => $searchValue !== '' ? $searchValue : null,
                        'user_role' => $roleValue !== '' ? $roleValue : null,
                        'user_status' => $statusValue !== '' ? $statusValue : null,
                    ]));

                    $queryPrefix = $queryString !== '' ? "{$queryString}&" : '';
                @endphp

                @if ($usersPaginator->onFirstPage())
                    <span class="rounded-lg border border-gray-300 px-4 py-2 text-xs font-bold uppercase text-gray-400">Previous</span>
                @else
                    <a
                        href="{{ $basePaginationUrl }}?{{ $queryPrefix }}user_page={{ $usersPaginator->currentPage() - 1 }}"
                        data-dashboard-pagination
                        class="rounded-lg border border-[#1F6336]/30 px-4 py-2 text-xs font-bold uppercase text-[#1F6336] transition-all hover:bg-[#1F6336]/10"
                    >
                        Previous
                    </a>
                @endif

                @if ($usersPaginator->hasMorePages())
                    <a
                        href="{{ $basePaginationUrl }}?{{ $queryPrefix }}user_page={{ $usersPaginator->currentPage() + 1 }}"
                        data-dashboard-pagination
                        class="rounded-lg border border-[#1F6336]/30 px-4 py-2 text-xs font-bold uppercase text-[#1F6336] transition-all hover:bg-[#1F6336]/10"
                    >
                        Next
                    </a>
                @else
                    <span class="rounded-lg border border-gray-300 px-4 py-2 text-xs font-bold uppercase text-gray-400">Next</span>
                @endif
            </div>
        </div>
    @endif
</div>
