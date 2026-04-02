@props([
    'user',
])

@php
    $userId = (int) ($user->user_id ?? 0);
    $status = strtolower((string) ($user->status ?? 'inactive'));
    $toggleLabel = $status === 'active' ? 'Deactivate Account' : 'Activate Account';
@endphp

<div class="relative inline-flex items-center justify-end">
    <details class="group relative" data-user-actions-menu>
        <summary class="inline-flex h-8 w-8 list-none cursor-pointer items-center justify-center rounded-md border border-[#1F6336]/20 bg-white text-[#1F6336] transition hover:bg-[#F0F3EF] [&::-webkit-details-marker]:hidden" aria-label="User actions menu">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4">
                <path d="M12 7a1.75 1.75 0 1 0 0-3.5A1.75 1.75 0 0 0 12 7Zm0 7a1.75 1.75 0 1 0 0-3.5A1.75 1.75 0 0 0 12 14Zm0 7a1.75 1.75 0 1 0 0-3.5A1.75 1.75 0 0 0 12 21Z" />
            </svg>
        </summary>

        <div class="absolute right-0 z-20 mt-2 w-56 rounded-md border border-[#1F6336]/15 bg-white p-1.5 shadow-xl">
            <button
                type="button"
                class="flex w-full items-center rounded-md px-3 py-2 text-left text-xs font-semibold uppercase text-[#1F6336] transition hover:bg-[#F0F3EF]"
                onclick="document.getElementById('view-user-window-{{ $userId }}')?.setAttribute('open','open'); this.closest('details')?.removeAttribute('open');"
            >
                View Details
            </button>

            <div class="relative">
                <div class="flex w-full items-center rounded-md px-3 py-2 text-left text-xs font-semibold uppercase text-[#1F6336]">
                    Edit User
                </div>

                <div class="space-y-1 pb-1 pl-5">
                    <button
                        type="button"
                        class="flex w-full items-center rounded-md px-3 py-2 text-left text-[11px] font-semibold uppercase text-[#1F6336] transition hover:bg-[#F0F3EF]"
                        onclick="document.getElementById('reset-password-user-window-{{ $userId }}')?.setAttribute('open','open'); this.closest('[data-user-actions-menu]')?.removeAttribute('open');"
                    >
                        Reset Password
                    </button>

                    <button
                        type="button"
                        class="flex w-full items-center rounded-md px-3 py-2 text-left text-[11px] font-semibold uppercase text-[#1F6336] transition hover:bg-[#F0F3EF]"
                        onclick="document.getElementById('toggle-status-user-window-{{ $userId }}')?.setAttribute('open','open'); this.closest('[data-user-actions-menu]')?.removeAttribute('open');"
                    >
                        {{ $toggleLabel }}
                    </button>
                </div>
            </div>

            <button
                type="button"
                class="flex w-full items-center rounded-md px-3 py-2 text-left text-xs font-semibold uppercase text-red-700 transition hover:bg-red-50"
                onclick="document.getElementById('delete-user-window-{{ $userId }}')?.setAttribute('open','open'); this.closest('details')?.removeAttribute('open');"
            >
                Delete User
            </button>
        </div>
    </details>

    <x-shared.user-action-view-details-window :user="$user" />
    <x-shared.user-action-reset-password-window :user="$user" />
    <x-shared.user-action-toggle-status-window :user="$user" />
    <x-shared.user-action-delete-window :user="$user" />
</div>
