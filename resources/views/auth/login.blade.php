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

            <section class="relative z-10 flex w-full flex-col rounded-xl border border-[#1F6336]/20 bg-white px-4 shadow-xl sm:w-[30rem]">
                <div class="p-6">
                    <div class="mb-10 flex items-center justify-center overflow-hidden">
                        <a href="{{ url('/') }}" class="flex items-center gap-2 no-underline">
                            <span class="text-3xl font-black tracking-tight text-[#1F6336]">AICS Program | DSWD</span>
                        </a>
                    </div>

                    <h1 class="text-center mb-2 text-xl font-semibold text-gray-800 xl:text-2xl">Welcome to AICS Program</h1>
                    <p class="text-center mb-6 text-sm text-gray-500">Sign in with your staff account to access and manage applications.</p>

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

                        <button
                            type="submit"
                            class="w-full rounded-md border border-[#3DA814] bg-[#3DA814] px-5 py-2 text-sm font-medium text-white shadow transition hover:border-[#1F6336] hover:bg-[#1F6336]"
                        >
                            Sign in
                        </button>
                    </form>

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
