@props([
    'action' => null,
])

@php
    $submitAction = is_string($action) && $action !== '' ? $action : '#';

    $roleOptions = [
        'admin' => 'Admin',
        'aics_staff' => 'AICS Staff',
        'mswd_officer' => 'MSWD Officer',
        'mayor_office_staff' => 'Mayor\'s Office Staff',
        'accountant' => 'Accountant',
        'treasurer' => 'Treasurer',
    ];

@endphp

<details class="group relative">
    <summary class="inline-flex h-10 list-none cursor-pointer items-center justify-center whitespace-nowrap rounded-lg border border-[#3DA814] bg-[#3DA814] px-4 text-sm font-medium text-white shadow-sm transition-colors duration-150 hover:border-[#1F6336] hover:bg-[#1F6336] focus:outline-none focus:ring-2 focus:ring-[#3DA814]/30 [&::-webkit-details-marker]:hidden">
        Add User
    </summary>

    <div class="fixed inset-0 z-[110] hidden items-center justify-center bg-black/40 p-4 group-open:flex">
        <button
            type="button"
            aria-label="Close add user window"
            class="absolute inset-0 h-full w-full cursor-default"
            onclick="this.closest('details')?.removeAttribute('open')"
        ></button>

        <div class="relative z-[111] w-full max-w-2xl rounded-xl space-y-4 border border-[#1F6336]/20 bg-white p-6 shadow-2xl md:p-7">
            <div class="mb-5 flex items-center justify-between">
                <h3 class="text-base font-semibold text-[#1F6336]">Add user</h3>
                <button
                    type="button"
                    aria-label="Close add user window"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-[#1F6336]/20 text-[#1F6336] transition hover:bg-[#F0F3EF]"
                    onclick="this.closest('details')?.removeAttribute('open')"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ $submitAction }}" class="mt-6 space-y-6" data-add-user-form @if($submitAction === '#') onsubmit="event.preventDefault();" @endif>
                @csrf
                <input type="hidden" name="status" value="active" data-default-status>

                @if ($errors->any())
                    <div class="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                        <ul class="list-disc space-y-1 pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label for="add-user-first-name" class="mb-2 block text-xs font-semibold uppercase text-[#1F6336]">First name</label>
                        <input id="add-user-first-name" name="first_name" type="text" value="{{ old('first_name') }}" required minlength="2" maxlength="60" autocomplete="given-name" data-sanitize="name" class="w-full rounded-md border border-[#1F6336]/20 bg-white px-3 py-2 text-sm text-gray-700 outline-none transition focus:border-[#3DA814] focus:ring-2 focus:ring-[#3DA814]/20">
                        <p class="mt-2 hidden text-xs font-medium text-red-600" data-error-for="first_name"></p>
                    </div>

                    <div>
                        <label for="add-user-last-name" class="mb-2 block text-xs font-semibold uppercase text-[#1F6336]">Last name</label>
                        <input id="add-user-last-name" name="last_name" type="text" value="{{ old('last_name') }}" required minlength="2" maxlength="60" autocomplete="family-name" data-sanitize="name" class="w-full rounded-md border border-[#1F6336]/20 bg-white px-3 py-2 text-sm text-gray-700 outline-none transition focus:border-[#3DA814] focus:ring-2 focus:ring-[#3DA814]/20">
                        <p class="mt-2 hidden text-xs font-medium text-red-600" data-error-for="last_name"></p>
                    </div>
                </div>

                <div>
                    <label for="add-user-email" class="mb-2 block text-xs font-semibold uppercase text-[#1F6336]">Email</label>
                    <input id="add-user-email" name="email" type="email" value="{{ old('email') }}" required maxlength="120" autocomplete="email" pattern="^[^\s@]+@[^\s@]+\.[^\s@]+$" data-sanitize="email" class="w-full rounded-md border border-[#1F6336]/20 bg-white px-3 py-2 text-sm text-gray-700 outline-none transition focus:border-[#3DA814] focus:ring-2 focus:ring-[#3DA814]/20">
                    <p class="mt-2 hidden text-xs font-medium text-red-600" data-error-for="email"></p>
                </div>

                <div>
                    <label for="add-user-password" class="mb-2 block text-xs font-semibold uppercase text-[#1F6336]">Password</label>
                    <div class="relative">
                        <input id="add-user-password" name="password" type="password" required minlength="6" maxlength="128" autocomplete="new-password" data-sanitize="password" class="w-full rounded-md border border-[#1F6336]/20 bg-white px-3 py-2 pr-24 text-sm text-gray-700 outline-none transition focus:border-[#3DA814] focus:ring-2 focus:ring-[#3DA814]/20">
                        <button
                            type="button"
                            data-toggle-password
                            data-target="add-user-password"
                            class="absolute right-2 top-1/2 -translate-y-1/2 rounded-md border border-[#1F6336]/20 px-2.5 py-1 text-[11px] font-semibold uppercase text-[#1F6336] transition hover:bg-[#F0F3EF]"
                            aria-label="Show password"
                            aria-pressed="false"
                        >
                            Show
                        </button>
                    </div>
                    <p class="mt-2 hidden text-xs font-medium text-red-600" data-error-for="password"></p>
                </div>

                <div>
                    <label for="add-user-role" class="mb-2 block text-xs font-semibold uppercase text-[#1F6336]">Role</label>
                    <select id="add-user-role" name="role" required class="w-full rounded-md border border-[#1F6336]/20 bg-white px-3 py-2 text-sm text-gray-700 outline-none transition focus:border-[#3DA814] focus:ring-2 focus:ring-[#3DA814]/20">
                        @foreach ($roleOptions as $value => $label)
                            <option value="{{ $value }}" @selected(old('role') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="mt-2 hidden text-xs font-medium text-red-600" data-error-for="role"></p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3">
                    <button
                        type="button"
                        class="rounded-md border border-[#1F6336]/20 px-3 py-2 text-xs font-semibold uppercase text-[#1F6336] transition hover:bg-[#F0F3EF]"
                        onclick="this.closest('details')?.removeAttribute('open')"
                    >
                        Cancel
                    </button>
                    <button type="submit" class="rounded-md bg-[#1F6336] px-3 py-2 text-xs font-semibold uppercase text-white transition hover:bg-[#184D2A]">
                        Save User
                    </button>
                </div>
            </form>
        </div>
    </div>
</details>