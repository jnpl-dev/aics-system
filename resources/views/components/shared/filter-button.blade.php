@props([
    'label' => 'Filters',
    'withIcon' => true,
])

@php
    $classes = 'inline-flex h-10 list-none cursor-pointer items-center justify-center whitespace-nowrap rounded-lg border border-[#1F6336]/25 bg-white px-3 text-sm font-medium text-[#1F6336] shadow-sm transition-colors duration-150 hover:border-[#1F6336]/40 hover:bg-[#F0F3EF] focus:outline-none focus:ring-2 focus:ring-[#3DA814]/30 [&::-webkit-details-marker]:hidden';
@endphp

<summary {{ $attributes->merge(['class' => $classes]) }}>
    @if ($withIcon)
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mr-2 h-4 w-4" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5 2.245 5 5v1.172l2.364 2.364a1 1 0 0 1-.707 1.707H5.343a1 1 0 0 1-.707-1.707L7 9.172V8c0-2.755 2.245-5 5-5Zm-3 12h6v2a3 3 0 1 1-6 0v-2Z" />
        </svg>
    @endif

    <span>{{ $label }}</span>
</summary>