@props([
    'id',
    'name',
    'label' => 'Filter',
    'selected' => '',
    'options' => [],
    'placeholder' => 'Choose an option',
    'preserve' => [],
])

@php
    $selectedValue = (string) $selected;
    $selectedLabel = $placeholder;

    foreach ($options as $optionValue => $optionLabel) {
        if ((string) $optionValue === $selectedValue) {
            $selectedLabel = (string) $optionLabel;
            break;
        }
    }
@endphp

<div>
    @foreach ($preserve as $preserveName => $preserveValue)
        @if ((string) $preserveValue !== '')
            <input type="hidden" name="{{ $preserveName }}" value="{{ $preserveValue }}">
        @endif
    @endforeach

    <details class="group relative w-full">
        <summary
            id="{{ $id }}"
            class="flex list-none items-center gap-2 border-b border-[#1F6336]/25 pb-1 text-[#1F6336] transition-colors hover:border-[#1F6336]/45 hover:text-[#184D2A] [&::-webkit-details-marker]:hidden"
        >
            <span class="text-sm font-medium">{{ $label }}:</span>
            <span class="text-sm text-slate-600">
                {{ $selectedLabel }}
            </span>
            <span class="transition-transform group-open:-rotate-180">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"></path>
                </svg>
            </span>
        </summary>

        <div class="z-auto w-64 divide-y divide-[#1F6336]/15 rounded border border-[#1F6336]/25 bg-white shadow-sm group-open:absolute group-open:start-0 group-open:top-8">
            <div class="flex items-center justify-between px-3 py-2">
                <span class="text-sm text-slate-600">
                    {{ $selectedValue === '' ? '0 Selected' : '1 Selected' }}
                </span>

                <button
                    type="submit"
                    name="{{ $name }}"
                    value=""
                    class="text-sm text-[#1F6336] underline transition-colors hover:text-[#184D2A]"
                >
                    Reset
                </button>
            </div>

            <fieldset class="p-3">
                <legend class="sr-only">{{ $label }} options</legend>

                <div class="flex flex-col items-start gap-3">
                    @foreach ($options as $optionValue => $optionLabel)
                        <button
                            type="submit"
                            name="{{ $name }}"
                            value="{{ $optionValue }}"
                            class="inline-flex items-center gap-3"
                        >
                            <span class="flex h-5 w-5 items-center justify-center rounded border border-[#1F6336]/30 shadow-sm {{ $selectedValue === (string) $optionValue ? 'bg-[#1F6336] text-white' : 'bg-white text-transparent' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5">
                                    <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-8 8a1 1 0 0 1-1.42 0l-4-4a1 1 0 1 1 1.414-1.414L8 12.586l7.296-7.296a1 1 0 0 1 1.408 0Z" clip-rule="evenodd" />
                                </svg>
                            </span>

                            <span class="text-sm font-medium text-slate-700">{{ $optionLabel }}</span>
                        </button>
                    @endforeach
                </div>
            </fieldset>
        </div>
    </details>
</div>