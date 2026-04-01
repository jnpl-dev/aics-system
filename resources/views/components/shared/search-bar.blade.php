@props([
    'id' => 'search-bar',
    'name' => 'search',
    'placeholder' => 'Search users...',
    'value' => '',
    'buttonText' => 'Search',
])

<div class="w-full min-w-[200px]">
    <div class="relative">
        <input
            id="{{ $id }}"
            name="{{ $name }}"
            data-live-search-input
            value="{{ $value }}"
            placeholder="{{ $placeholder }}"
            class="h-10 w-full rounded-md border border-[#1F6336]/20 bg-transparent pl-3 pr-28 text-sm text-slate-700 shadow-sm transition duration-300 ease-in-out placeholder:text-slate-400 hover:border-[#1F6336]/35 focus:border-[#3DA814] focus:shadow focus:outline-none"
        >

        <button
            type="submit"
            class="absolute right-1 top-1 inline-flex h-8 items-center rounded bg-[#1F6336] px-2.5 text-center text-sm text-white shadow-sm transition-all hover:bg-[#184D2A] hover:shadow focus:bg-[#184D2A] focus:shadow-none"
        >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="mr-2 h-4 w-4">
                <path fill-rule="evenodd" d="M10.5 3.75a6.75 6.75 0 1 0 0 13.5 6.75 6.75 0 0 0 0-13.5ZM2.25 10.5a8.25 8.25 0 1 1 14.59 5.28l4.69 4.69a.75.75 0 1 1-1.06 1.06l-4.69-4.69A8.25 8.25 0 0 1 2.25 10.5Z" clip-rule="evenodd" />
            </svg>
            {{ $buttonText }}
        </button>
    </div>
</div>