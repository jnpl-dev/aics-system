@props([
    'active' => false,
    'ariaLabel' => null,
    'disabled' => false,
    'icon' => null,
    'iconAlias' => null,
    'label' => null,
])

@php
    $formatNumber = fn (int|float|string $value): string => extension_loaded('intl')
        ? \Illuminate\Support\Number::format((float) $value, locale: app()->getLocale())
        : number_format((float) $value);
@endphp

<li
    {{
        $attributes->class([
            'fi-pagination-item',
            'fi-disabled' => $disabled,
            'fi-active' => $active,
        ])
    }}
>
    <button
        aria-label="{{ $ariaLabel }}"
        @disabled($disabled)
        type="button"
        class="fi-pagination-item-btn"
    >
        @if (filled($icon))
            {{
                \Filament\Support\generate_icon_html($icon, $iconAlias, attributes: (new \Illuminate\View\ComponentAttributeBag)->class([
                    'fi-pagination-item-icon',
                ]))
            }}
        @endif

        @if (filled($label))
            <span class="fi-pagination-item-label">
                {{ is_numeric($label) ? $formatNumber($label) : ($label ?? '...') }}
            </span>
        @endif
    </button>
</li>
