@props([
    'user',
])

@php
    $userId = (int) ($user->user_id ?? 0);
    $windowId = "view-user-window-{$userId}";
    $fullName = trim(((string) ($user->first_name ?? '')).' '.((string) ($user->last_name ?? '')));
    $displayName = $fullName !== '' ? $fullName : 'N/A';
    $role = str_replace('_', ' ', (string) ($user->role ?? '-'));
    $status = strtolower((string) ($user->status ?? 'unknown'));
@endphp

<details id="{{ $windowId }}" class="group relative">
    <summary class="hidden">View details</summary>

    <div class="fixed inset-0 z-[120] hidden items-center justify-center bg-black/40 p-4 group-open:flex">
        <button
            type="button"
            aria-label="Close view user details window"
            class="absolute inset-0 h-full w-full cursor-default"
            onclick="this.closest('details')?.removeAttribute('open')"
        ></button>

    <div class="relative z-[121] w-full max-w-xl rounded-xl border border-[#1F6336]/20 bg-white p-6 text-left shadow-2xl md:p-7">
            <div class="mb-5 flex items-center justify-between">
                <h3 class="text-base font-semibold text-[#1F6336]">User details</h3>
                <button
                    type="button"
                    aria-label="Close view user details window"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-[#1F6336]/20 text-[#1F6336] transition hover:bg-[#F0F3EF]"
                    onclick="this.closest('details')?.removeAttribute('open')"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <dl class="grid grid-cols-1 gap-4 text-sm text-gray-700 md:grid-cols-2">
                <div class="rounded-md border border-[#1F6336]/10 bg-[#F8FBF8] p-3">
                    <dt class="text-xs font-semibold uppercase text-[#1F6336]">User ID</dt>
                    <dd class="mt-1 font-medium">#{{ $userId }}</dd>
                </div>
                <div class="rounded-md border border-[#1F6336]/10 bg-[#F8FBF8] p-3">
                    <dt class="text-xs font-semibold uppercase text-[#1F6336]">Status</dt>
                    <dd class="mt-1 font-medium uppercase">{{ $status }}</dd>
                </div>
                <div class="rounded-md border border-[#1F6336]/10 bg-[#F8FBF8] p-3 md:col-span-2">
                    <dt class="text-xs font-semibold uppercase text-[#1F6336]">Name</dt>
                    <dd class="mt-1 font-medium">{{ $displayName }}</dd>
                </div>
                <div class="rounded-md border border-[#1F6336]/10 bg-[#F8FBF8] p-3 md:col-span-2">
                    <dt class="text-xs font-semibold uppercase text-[#1F6336]">Email</dt>
                    <dd class="mt-1 font-medium break-all">{{ (string) ($user->email ?? '-') }}</dd>
                </div>
                <div class="rounded-md border border-[#1F6336]/10 bg-[#F8FBF8] p-3 md:col-span-2">
                    <dt class="text-xs font-semibold uppercase text-[#1F6336]">Role</dt>
                    <dd class="mt-1 font-medium">{{ $role }}</dd>
                </div>
            </dl>

            <div class="mt-6 flex items-center justify-end">
                <button
                    type="button"
                    class="rounded-md bg-[#1F6336] px-4 py-2 text-xs font-semibold uppercase text-white transition hover:bg-[#184D2A]"
                    onclick="this.closest('details')?.removeAttribute('open')"
                >
                    Close
                </button>
            </div>
        </div>
    </div>
</details>
