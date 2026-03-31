<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AICS Staff Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen w-screen bg-[#F0F3EF] text-gray-700">
    <main class="flex min-h-screen w-full items-center justify-center p-4">
        <div class="relative">
            <div class="absolute -left-20 -top-20 z-0 hidden h-56 w-56 text-[#3DA814]/25 sm:block">
                <svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <pattern id="aics-pattern-a" patternUnits="userSpaceOnUse" width="40" height="40" patternTransform="scale(0.6)">
                            <rect x="0" y="0" width="100%" height="100%" fill="none" />
                            <path d="M11 6a5 5 0 01-5 5 5 5 0 01-5-5 5 5 0 015-5 5 5 0 015 5" fill="currentColor" />
                        </pattern>
                    </defs>
                    <rect width="800%" height="800%" fill="url(#aics-pattern-a)" />
                </svg>
            </div>

            <div class="absolute -bottom-20 -right-20 z-0 hidden h-28 w-28 text-[#3DA814]/25 sm:block">
                <svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <pattern id="aics-pattern-b" patternUnits="userSpaceOnUse" width="40" height="40" patternTransform="scale(0.5)">
                            <rect x="0" y="0" width="100%" height="100%" fill="none" />
                            <path d="M11 6a5 5 0 01-5 5 5 5 0 01-5-5 5 5 0 015-5 5 5 0 015 5" fill="currentColor" />
                        </pattern>
                    </defs>
                    <rect width="800%" height="800%" fill="url(#aics-pattern-b)" />
                </svg>
            </div>

            <section id="auth-card" class="relative z-10 flex w-full flex-col rounded-xl border border-[#1F6336]/20 bg-white px-4 shadow-xl" style="max-width: 30rem;">
                <div class="p-6">
                    <div class="mb-10 flex items-center justify-center overflow-hidden">
                        <a href="{{ url('/') }}" class="flex items-center gap-2 no-underline">
                            <span class="text-3xl font-black tracking-tight text-[#1F6336]">AICS Program | DSWD</span>
                        </a>
                    </div>

                    <h1 id="login-step-title" class="text-center mb-2 text-xl font-semibold text-gray-800 xl:text-2xl">Welcome to AICS Program</h1>
                    <p id="login-step-subtitle" class="text-center mb-6 text-sm text-gray-500">Sign in with your staff account to access and manage applications.</p>

                    <form id="supabase-login-form" class="mb-4" method="POST" action="#" onsubmit="return false;">
                        <div class="mb-4">
                            <label for="email" class="mb-2 inline-block text-xs font-semibold uppercase tracking-wide text-gray-700">Email</label>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                required
                                autofocus
                                placeholder="Enter your email"
                                class="block w-full appearance-none rounded-md border border-gray-300 bg-[#F0F3EF] px-3 py-2 text-sm text-gray-700 outline-none transition focus:border-[#3DA814] focus:bg-white focus:shadow"
                            />
                        </div>

                        <div class="mb-4">
                            <div class="mb-2 flex items-center justify-between">
                                <label for="password" class="inline-block text-xs font-semibold uppercase tracking-wide text-gray-700">Password</label>
                                <a href="#" class="text-xs text-[#3DA814] hover:text-[#1F6336]">Forgot Password?</a>
                            </div>
                            <div class="relative">
                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    required
                                    placeholder="••••••••••••"
                                    class="block w-full appearance-none rounded-md border border-gray-300 bg-[#F0F3EF] px-3 py-2 pr-24 text-sm text-gray-700 outline-none transition focus:border-[#3DA814] focus:bg-white focus:shadow"
                                />
                                <button
                                    id="toggle-password"
                                    type="button"
                                    class="absolute inset-y-0 right-0 px-3 text-xs font-medium text-[#1F6336] hover:text-[#3DA814]"
                                    aria-label="Show password"
                                    aria-pressed="false"
                                >
                                    Show
                                </button>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="remember-me" class="inline-flex items-center gap-2 text-sm text-gray-600">
                                <input
                                    id="remember-me"
                                    type="checkbox"
                                    checked
                                    class="h-4 w-4 cursor-pointer rounded border-gray-300 accent-[#1F6336] focus:ring-2 focus:ring-[#3DA814]/30"
                                />
                                <span>Remember me</span>
                            </label>
                        </div>

                        <x-shared.button
                            id="password-submit-btn"
                            type="submit"
                            variant="primary"
                            loading-text="Signing in..."
                            :full-width="true"
                            class="rounded-md px-5 py-2 shadow"
                        >
                            Sign in
                        </x-shared.button>
                    </form>

                    <section id="otp-section" class="hidden mb-4 rounded-xl border border-[#1F6336]/15 bg-[#F0F3EF]/40 p-4 sm:p-6 shadow-sm">
                        <div class="text-center">
                            <h2 class="text-lg sm:text-xl font-bold text-[#1F6336]">Email Verification</h2>
                            <p id="otp-help-text" class="mt-1 text-sm text-gray-600">Enter the 6-digit verification code sent to your email.</p>
                        </div>

                        <input id="otp-code" type="hidden" name="otp_code" autocomplete="one-time-code" />

                        <div id="otp-digit-group" class="mt-5 mx-auto flex w-full max-w-[24rem] items-center justify-center gap-3 sm:gap-4">
                            @for ($index = 0; $index < 6; $index++)
                                <input
                                    type="text"
                                    inputmode="numeric"
                                    pattern="\d*"
                                    maxlength="1"
                                    data-otp-digit
                                    data-otp-index="{{ $index }}"
                                    class="h-11 w-11 sm:h-12 sm:w-12 text-center text-lg sm:text-xl font-extrabold text-[#1F6336] bg-white border border-[#1F6336]/15 hover:border-[#1F6336]/30 rounded-lg outline-none transition focus:bg-white focus:border-[#3DA814] focus:ring-2 focus:ring-[#3DA814]/20"
                                    aria-label="OTP digit {{ $index + 1 }}"
                                />
                            @endfor
                        </div>

                        <div class="max-w-[280px] mx-auto mt-5">
                            <x-shared.button id="otp-verify-btn" type="button" variant="primary" loading-text="Verifying..." :full-width="true">
                                Verify Account
                            </x-shared.button>
                        </div>

                        <div class="mt-4 text-center text-sm text-gray-500">
                            Didn't receive code?
                            <x-shared.button id="otp-resend-btn" type="button" variant="tertiary" loading-text="Resending..." class="font-medium">
                                Resend
                            </x-shared.button>
                        </div>

                        <div class="mt-3 text-center">
                            <x-shared.button id="otp-back-btn" type="button" variant="tertiary" class="text-xs font-medium text-gray-500 hover:text-gray-700 focus:ring-0">
                                Use a different account
                            </x-shared.button>
                        </div>
                    </section>

                    <div id="auth-status" class="hidden rounded-md border px-3 py-2 text-sm"></div>

                    <p class="mt-4 text-center text-sm text-gray-500">
                        Authorized staff access only.
                        <span class="text-[#1F6336]"><br>AICS Digital Application and Notification System</span>
                    </p>
                </div>
            </section>
        </div>
    </main>

    <script>
        window.__AICS_SUPABASE__ = {
            url: @json($supabaseUrl),
            anonKey: @json($supabaseAnonKey),
        };

        (() => {
            const passwordInput = document.getElementById('password');
            const toggleButton = document.getElementById('toggle-password');

            if (!passwordInput || !toggleButton) return;

            toggleButton.addEventListener('click', () => {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                toggleButton.textContent = isPassword ? 'Hide' : 'Show';
                toggleButton.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
                toggleButton.setAttribute('aria-pressed', isPassword ? 'true' : 'false');
            });
        })();
    </script>
</body>
</html>
