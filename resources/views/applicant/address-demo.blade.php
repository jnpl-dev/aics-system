<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Address Selector Demo | {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#FFFDFF] text-[#176334]">
    <main class="mx-auto max-w-4xl space-y-6 p-6 lg:p-10">
        <header class="rounded-xl border border-[#176334]/20 bg-white p-6 shadow-sm">
            <h1 class="text-2xl font-semibold">Reusable PH Address Selector</h1>
            <p class="mt-2 text-sm text-[#176334]/75">
                This standalone page demonstrates the componentized Region → Province → City/Municipality → Barangay selector.
            </p>
        </header>

        <x-forms.page-feedback />

        <form method="POST" action="#" class="rounded-xl border border-[#176334]/20 bg-white p-6 shadow-sm" novalidate>
            @csrf

            <div class="space-y-8">
                <x-forms.ph-address-selector
                    prefix="demo-applicant"
                    name="demo[applicant_address]"
                    label="Applicant Address"
                    :value="old('demo.applicant_address')"
                />

                <x-forms.ph-address-selector
                    prefix="demo-beneficiary"
                    name="demo[beneficiary_address]"
                    label="Beneficiary Address"
                    :value="old('demo.beneficiary_address')"
                />
            </div>

            <div class="mt-8 flex items-center justify-between">
                <a href="{{ route('applicant.apply') }}" class="text-sm font-semibold text-[#176334]">Back to Apply Form</a>
                <button type="button" class="rounded-md bg-[#176334] px-4 py-2 text-sm font-semibold text-white hover:opacity-90">Demo Only</button>
            </div>
        </form>
    </main>
</body>
</html>
