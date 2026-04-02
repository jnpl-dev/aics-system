@props([
    'user',
])

@php
    $userId = (int) ($user->user_id ?? 0);
    $windowId = "reset-password-user-window-{$userId}";
    $displayName = trim(((string) ($user->first_name ?? '')).' '.((string) ($user->last_name ?? '')));
@endphp

<details id="{{ $windowId }}" class="group relative">
    <summary class="hidden">Reset user password</summary>

    <div class="fixed inset-0 z-[120] hidden items-center justify-center bg-black/40 p-4 group-open:flex">
        <button
            type="button"
            aria-label="Close reset password window"
            class="absolute inset-0 h-full w-full cursor-default"
            onclick="this.closest('details')?.removeAttribute('open')"
        ></button>

    <div class="relative z-[121] w-full max-w-lg rounded-xl border border-[#1F6336]/20 bg-white p-6 text-left shadow-2xl md:p-7">
            <div class="mb-5 flex items-center justify-between">
                <h3 class="text-base font-semibold text-[#1F6336]">Reset password</h3>
                <button
                    type="button"
                    aria-label="Close reset password window"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-[#1F6336]/20 text-[#1F6336] transition hover:bg-[#F0F3EF]"
                    onclick="this.closest('details')?.removeAttribute('open')"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <p class="mb-4 text-sm text-gray-600">
                Prepare a password reset for
                <span class="font-semibold text-[#1F6336]">{{ $displayName !== '' ? $displayName : ((string) ($user->email ?? 'this user')) }}</span>.
            </p>

            <form method="POST" action="#" onsubmit="event.preventDefault();" class="space-y-4">
                <div>
                    <label for="reset-user-password-{{ $userId }}" class="mb-2 block text-xs font-semibold uppercase text-[#1F6336]">New password</label>
                    <input
                        id="reset-user-password-{{ $userId }}"
                        type="password"
                        minlength="6"
                        maxlength="128"
                        class="w-full rounded-md border border-[#1F6336]/20 bg-white px-3 py-2 text-sm text-gray-700 outline-none transition focus:border-[#3DA814] focus:ring-2 focus:ring-[#3DA814]/20"
                        placeholder="Enter new password"
                    >
                </div>

                <div>
                    <label for="reset-user-password-confirm-{{ $userId }}" class="mb-2 block text-xs font-semibold uppercase text-[#1F6336]">Confirm password</label>
                    <input
                        id="reset-user-password-confirm-{{ $userId }}"
                        type="password"
                        minlength="6"
                        maxlength="128"
                        class="w-full rounded-md border border-[#1F6336]/20 bg-white px-3 py-2 text-sm text-gray-700 outline-none transition focus:border-[#3DA814] focus:ring-2 focus:ring-[#3DA814]/20"
                        placeholder="Confirm new password"
                    >
                </div>

                <p class="text-xs text-gray-500">
                    Window component is ready. Connect this to backend reset-password endpoint when implementation starts.
                </p>

                <div class="flex items-center justify-end gap-3 pt-2">
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
                        Confirm Reset
                    </button>
                </div>
            </form>
        </div>
    </div>
</details>
