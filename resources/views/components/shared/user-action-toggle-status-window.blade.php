@props([
    'user',
])

@php
    $userId = (int) ($user->user_id ?? 0);
    $windowId = "toggle-status-user-window-{$userId}";
    $displayName = trim(((string) ($user->first_name ?? '')).' '.((string) ($user->last_name ?? '')));
    $status = strtolower((string) ($user->status ?? 'inactive'));
    $isActive = $status === 'active';
    $verb = $isActive ? 'Deactivate' : 'Activate';
@endphp

<details id="{{ $windowId }}" class="group relative">
    <summary class="hidden">Toggle user status</summary>

    <div class="fixed inset-0 z-[120] hidden items-center justify-center bg-black/40 p-4 group-open:flex">
        <button
            type="button"
            aria-label="Close status window"
            class="absolute inset-0 h-full w-full cursor-default"
            onclick="this.closest('details')?.removeAttribute('open')"
        ></button>

    <div class="relative z-[121] w-full max-w-lg rounded-xl border border-[#1F6336]/20 bg-white p-6 text-left shadow-2xl md:p-7">
            <div class="mb-5 flex items-center justify-between">
                <h3 class="text-base font-semibold text-[#1F6336]">{{ $verb }} account</h3>
                <button
                    type="button"
                    aria-label="Close status window"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-[#1F6336]/20 text-[#1F6336] transition hover:bg-[#F0F3EF]"
                    onclick="this.closest('details')?.removeAttribute('open')"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <p class="text-sm text-gray-600">
                Are you sure you want to
                <span class="font-semibold text-[#1F6336]">{{ strtolower($verb) }}</span>
                the account of
                <span class="font-semibold text-[#1F6336]">{{ $displayName !== '' ? $displayName : ((string) ($user->email ?? 'this user')) }}</span>?
            </p>

            <p class="mt-3 text-xs text-gray-500">
                Current status: <span class="font-semibold uppercase text-gray-700">{{ $status }}</span>
            </p>

            <form method="POST" action="#" onsubmit="event.preventDefault();" class="mt-6 flex items-center justify-end gap-3">
                <button
                    type="button"
                    class="rounded-md border border-[#1F6336]/20 px-3 py-2 text-xs font-semibold uppercase text-[#1F6336] transition hover:bg-[#F0F3EF]"
                    onclick="this.closest('details')?.removeAttribute('open')"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    class="rounded-md bg-[#1F6336] px-3 py-2 text-xs font-semibold uppercase text-white transition hover:bg-[#184D2A]"
                >
                    {{ $verb }} Account
                </button>
            </form>
        </div>
    </div>
</details>
