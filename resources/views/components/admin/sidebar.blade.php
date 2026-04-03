@props([
    'role' => 'admin',
    'fullName' => 'Loading user...',
    'activeTab' => 'dashboard',
])

@php
    $role = strtolower((string) $role);
    $role = $role === 'system_admin' ? 'admin' : $role;
    $activeTab = (string) $activeTab;

    $navItems = [
        [
            'key' => 'dashboard',
            'label' => 'Dashboard',
            'roles' => ['admin'],
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />',
        ],
    ];

    $visibleItems = array_values(array_filter($navItems, static fn (array $item): bool => in_array($role, $item['roles'], true)));
@endphp

<aside class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col overflow-hidden text-[#F0F3EF] bg-[#1F6336] shadow-xl rounded-r-lg">
    <div class="w-full px-4 pt-4 pb-3 border-b border-[#3DA814]/25">
        <a class="flex items-center" href="{{ route('dashboard', ['tab' => 'dashboard']) }}" data-dashboard-tab="dashboard">
            <svg class="w-8 h-8 fill-current text-[#3DA814]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path d="M11 17a1 1 0 001.447.894l4-2A1 1 0 0017 15V9.236a1 1 0 00-1.447-.894l-4 2a1 1 0 00-.553.894V17zM15.211 6.276a1 1 0 000-1.788l-4.764-2.382a1 1 0 00-.894 0L4.789 4.488a1 1 0 000 1.788l4.764 2.382a1 1 0 00.894 0l4.764-2.382zM4.447 8.342A1 1 0 003 9.236V15a1 1 0 00.553.894l4 2A1 1 0 009 17v-5.764a1 1 0 00-.553-.894l-4-2z" />
            </svg>
            <span class="ml-3 text-sm font-bold tracking-wide text-[#F0F3EF]">AICS Program | DSWD</span>
        </a>

        <div class="mt-3">
            <p id="sidebar-user-name" class="text-sm font-semibold text-[#F0F3EF] truncate">{{ $fullName }}</p>
            <p id="sidebar-user-role" class="text-xs uppercase tracking-wide text-[#F0F3EF]/70">{{ str_replace('_', ' ', $role) }}</p>
        </div>
    </div>

    <div class="w-full px-3">
        <div class="flex flex-col items-center w-full mt-4 border-[#3DA814]/35 pt-2">
            @foreach ($visibleItems as $item)
                <x-admin.sidebar-item
                    :label="$item['label']"
                    :href="route('dashboard', ['tab' => $item['key']])"
                    :tab-key="$item['key']"
                    :active="$activeTab === $item['key']"
                    :icon="$item['icon']"
                />
            @endforeach
        </div>
    </div>

    <details class="mt-auto w-full border-t border-[#3DA814]/25 bg-[#174e2b]">
        <summary class="flex cursor-pointer list-none items-center justify-start w-full h-16 px-4 hover:bg-[#145024] transition">
            <svg class="w-6 h-6 stroke-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="ml-3 text-sm font-medium">Account</span>
            <svg class="ml-auto h-4 w-4 opacity-70" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </summary>

        <div class="px-3 pb-3 space-y-2">
            <button id="logout-btn" type="button" class="flex w-full items-center rounded-md px-3 py-2 text-sm text-[#F0F3EF]/85 hover:bg-[#145024] hover:text-[#F0F3EF] transition">
                Logout
            </button>
        </div>
    </details>
</aside>
