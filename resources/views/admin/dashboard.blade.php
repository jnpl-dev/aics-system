<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle ?? 'Dashboard' }} | AICS Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#F0F3EF] text-gray-800">
    <main class="min-h-screen w-full">
        <x-admin.sidebar
            :role="$sidebarRole ?? 'admin'"
            :full-name="$sidebarFullName ?? 'Loading user...'"
            :active-tab="$activeTab ?? 'dashboard'"
        />

        <section class="ml-64 min-h-screen px-8 py-6">
            <span id="dashboard-page-title" class="sr-only">{{ $pageTitle ?? 'Dashboard' }}</span>

            <div
                id="dashboard-content"
                class="mt-1"
                data-active-tab="{{ $activeTab ?? 'dashboard' }}"
                data-dashboard-base-url="{{ route('dashboard') }}"
                data-content-endpoint-template="{{ route('dashboard.content', ['tab' => '__TAB__']) }}"
            >
                @include($initialTabView ?? 'admin.tabs.dashboard', [
                    'tabKey' => $activeTab ?? 'dashboard',
                    'pageTitle' => $pageTitle ?? 'Dashboard',
                ])
            </div>

            <div id="dashboard-content-loading" class="hidden mt-4 text-sm text-[#1F6336]/70">Loading section...</div>
        </section>
    </main>
</body>
</html>
