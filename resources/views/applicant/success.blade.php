<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Application Submitted | {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#FFFDFF] text-[#176334]">
    <main class="mx-auto max-w-3xl p-6 lg:p-10">
        <section class="rounded-xl border border-[#176334]/20 bg-white p-6 shadow-sm space-y-6">
            <header class="space-y-2">
                <h1 class="text-2xl font-semibold">Application Submitted Successfully</h1>
                <p class="text-sm text-[#176334]/75">
                    Keep your reference code below. You can use it on the tracking page to check the latest status of your application.
                </p>
            </header>

            <div class="rounded-lg border border-[#6C9C02]/40 bg-[#6C9C02]/10 p-4">
                <p class="text-xs uppercase tracking-wide text-[#176334]/75">Reference Code</p>
                <div class="mt-2 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p id="reference-code" class="text-lg font-semibold">{{ $referenceCode }}</p>
                    <button
                        id="copy-reference"
                        type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-md bg-[#176334] px-3 py-2 text-sm font-semibold text-white hover:opacity-90"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                        </svg>
                        <span id="copy-label">Copy code</span>
                    </button>
                </div>
                <p id="copy-feedback" class="mt-2 text-xs text-[#176334]/75" aria-live="polite"></p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('applicant.track') }}" class="inline-flex items-center justify-center rounded-md bg-[#6C9C02] px-4 py-2 text-sm font-semibold text-white hover:opacity-90">
                    Go to Track Application
                </a>
                <a href="{{ url('/') }}" class="inline-flex items-center justify-center rounded-md border border-[#176334]/30 bg-white px-4 py-2 text-sm font-semibold text-[#176334] hover:bg-[#176334]/5">
                    Back to Home
                </a>
            </div>
        </section>
    </main>

    <script>
        (() => {
            const copyButton = document.getElementById('copy-reference');
            const copyLabel = document.getElementById('copy-label');
            const copyFeedback = document.getElementById('copy-feedback');
            const referenceCode = document.getElementById('reference-code')?.textContent?.trim() ?? '';

            if (!copyButton || !referenceCode) {
                return;
            }

            copyButton.addEventListener('click', async () => {
                try {
                    await navigator.clipboard.writeText(referenceCode);
                    copyLabel.textContent = 'Copied!';
                    copyFeedback.textContent = 'Reference code copied to clipboard.';
                } catch (_) {
                    copyLabel.textContent = 'Copy failed';
                    copyFeedback.textContent = 'Clipboard access failed. Please copy the code manually.';
                }

                setTimeout(() => {
                    copyLabel.textContent = 'Copy code';
                }, 1800);
            });
        })();
    </script>
</body>
</html>
