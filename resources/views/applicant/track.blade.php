<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Track Application | {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#FFFDFF] text-[#176334] p-6">
    <main class="mx-auto max-w-3xl rounded-2xl border border-[#176334]/20 bg-white p-8 shadow">
        <h1 class="text-2xl font-semibold mb-3">Track Application</h1>
        <p class="text-[#176334]/80 mb-6">
            Tracking interface will be added here in the next step.
        </p>

        <a href="{{ url('/') }}" class="inline-flex rounded-lg bg-[#176334] px-4 py-2 text-white hover:opacity-90 transition">
            Back to Directory
        </a>
    </main>
</body>
</html>
