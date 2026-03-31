@props([
    'label',
    'href' => '#',
    'tabKey' => null,
    'active' => false,
    'icon',
])

@php
    $baseClasses = 'dashboard-tab-link group flex items-center w-full h-12 px-3 mt-2 rounded-md transition';
    $activeClasses = 'text-[#1F6336] bg-[#F0F3EF] border border-[#F0F3EF]/80 shadow-sm';
    $inactiveClasses = 'text-[#F0F3EF]/80 hover:bg-[#3DA814]/15 hover:text-[#F0F3EF]';
@endphp

<a
    href="{{ $href }}"
    @if ($tabKey)
        data-dashboard-tab="{{ $tabKey }}"
        data-tab-base-class="{{ $baseClasses }}"
        data-tab-active-class="{{ $activeClasses }}"
        data-tab-inactive-class="{{ $inactiveClasses }}"
    @endif
    class="{{ $baseClasses }} {{ $active ? $activeClasses : $inactiveClasses }}"
>
    <svg class="w-6 h-6 stroke-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
        {!! $icon !!}
    </svg>
    <span class="ml-3 text-sm font-medium">{{ $label }}</span>
</a>
