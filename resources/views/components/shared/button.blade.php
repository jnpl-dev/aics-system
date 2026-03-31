@props([
    'variant' => 'primary',
    'type' => 'button',
    'fullWidth' => false,
    'loadingText' => null,
])

@php
    $baseClasses = 'inline-flex items-center justify-center whitespace-nowrap rounded-lg text-sm font-medium transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-[#3DA814]/30 disabled:opacity-65 disabled:cursor-not-allowed';

    $sizeClasses = 'px-3.5 py-2.5';

    $variantClasses = match ($variant) {
        'primary' => 'border border-[#3DA814] bg-[#3DA814] text-white shadow-sm hover:border-[#1F6336] hover:bg-[#1F6336]',
        'secondary' => 'border border-[#1F6336]/25 bg-white text-[#1F6336] shadow-sm hover:border-[#1F6336]/40 hover:bg-[#F0F3EF]',
        'tertiary' => 'border border-transparent bg-transparent px-0 py-0 text-[#3DA814] hover:text-[#1F6336] underline-offset-2 hover:underline focus:ring-0',
        default => 'border border-[#3DA814] bg-[#3DA814] text-white shadow-sm hover:border-[#1F6336] hover:bg-[#1F6336]',
    };

    $widthClass = $fullWidth ? 'w-full' : '';
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge([
        'data-loading' => 'false',
        'aria-busy' => 'false',
        'data-loading-text' => $loadingText,
        'class' => trim("{$baseClasses} {$sizeClasses} {$variantClasses} {$widthClass}"),
    ]) }}
>
    <span data-btn-spinner class="mr-2 hidden" aria-hidden="true">
        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-90" d="M22 12a10 10 0 00-10-10" stroke="currentColor" stroke-width="4" stroke-linecap="round"></path>
        </svg>
    </span>
    <span data-btn-label>{{ $slot }}</span>
</button>
