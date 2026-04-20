<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Track Application | {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#FFFDFF] text-[#176334] p-6">
    <main class="mx-auto w-full max-w-5xl space-y-6">
        <section class="rounded-2xl border border-[#176334]/20 bg-white p-6 shadow md:p-8">
            <h1 class="text-2xl font-semibold mb-2">Track Application</h1>
            <p class="text-sm text-[#176334]/80 mb-6">
                Enter your reference number and applicant surname to view your application progress.
            </p>

            @if (session('status'))
                <div class="mb-4 rounded-lg border border-[#6C9C02]/40 bg-[#6C9C02]/10 px-4 py-3 text-sm text-[#176334]">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->hasAny(['reference_code', 'applicant_surname']) && ! $application)
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ $errors->first('reference_code') ?: $errors->first('applicant_surname') }}
                </div>
            @endif

            <form action="{{ route('applicant.track.access') }}" method="POST" class="grid gap-4 md:grid-cols-3 md:items-end">
                @csrf
                <input type="hidden" name="hp_token" value="">

                <div class="md:col-span-1">
                    <label for="reference_code" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-[#176334]/80">Reference Number</label>
                    <input
                        id="reference_code"
                        name="reference_code"
                        type="text"
                        value="{{ old('reference_code') }}"
                        placeholder="AICS-YYYYMMDD-000001"
                        class="w-full rounded-lg border border-[#176334]/30 px-3 py-2 text-sm text-[#176334] focus:border-[#176334] focus:outline-none focus:ring-2 focus:ring-[#176334]/20"
                        required
                    >
                    @error('reference_code')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-1">
                    <label for="applicant_surname" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-[#176334]/80">Applicant Surname</label>
                    <input
                        id="applicant_surname"
                        name="applicant_surname"
                        type="text"
                        value="{{ old('applicant_surname') }}"
                        placeholder="Dela Cruz"
                        class="w-full rounded-lg border border-[#176334]/30 px-3 py-2 text-sm text-[#176334] focus:border-[#176334] focus:outline-none focus:ring-2 focus:ring-[#176334]/20"
                        required
                    >
                    @error('applicant_surname')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-1">
                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center rounded-lg bg-[#176334] px-4 py-2.5 text-sm font-semibold text-white transition hover:opacity-90"
                    >
                        View Tracking Details
                    </button>
                </div>
            </form>
        </section>

        @if ($application)
            <section class="rounded-2xl border border-[#176334]/20 bg-white p-6 shadow md:p-8">
                <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-[#176334]/70">Reference Number</p>
                        <h2 class="text-lg font-semibold">{{ $application->reference_code }}</h2>
                    </div>
                    <div class="text-right">
                        <p class="text-xs uppercase tracking-wide text-[#176334]/70">Main Stage</p>
                        <p class="text-sm font-semibold text-[#176334]">{{ $mainStageLabel }}</p>
                        <p class="text-xs text-[#176334]/70">Current: {{ $statusLabel }}</p>
                    </div>
                </div>

                <div class="mb-8 grid grid-cols-4 gap-0 rounded-xl border border-[#176334]/15 bg-[#176334]/[0.03] p-4">
                    @foreach ($timeline as $stage)
                        @php
                            $isCompleted = $stage['state'] === 'completed';
                            $isCurrent = $stage['state'] === 'current';

                            $dotClasses = $isCompleted
                                ? 'border-emerald-500 bg-emerald-500'
                                : ($isCurrent
                                    ? 'border-[#2E7DFA] bg-white ring-2 ring-[#2E7DFA]/35'
                                    : 'border-[#176334]/25 bg-white');

                            $lineClasses = $isCompleted
                                ? 'bg-emerald-500'
                                : 'bg-[#176334]/20';
                        @endphp

                        <div class="relative min-w-0">
                            <div class="mb-2 flex items-center">
                                <span class="inline-flex h-4 w-4 shrink-0 rounded-full border-2 {{ $dotClasses }}"></span>
                                @if (! $loop->last)
                                    <span class="ml-2 h-0.5 flex-1 {{ $lineClasses }}"></span>
                                @endif
                            </div>
                            <p class="text-xs font-semibold uppercase tracking-wide {{ $isCurrent ? 'text-[#176334]' : 'text-[#176334]/65' }}">
                                {{ $stage['label'] }}
                            </p>
                        </div>
                    @endforeach
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-xl border border-[#176334]/15 p-4">
                        <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-[#176334]/70">Application Summary</p>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between gap-4">
                                <dt class="text-[#176334]/70">Applicant</dt>
                                <dd class="font-medium text-right">{{ $application->applicant_first_name }} {{ $application->applicant_last_name }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-[#176334]/70">Assistance Category</dt>
                                <dd class="font-medium text-right">{{ $application->category?->name ?? 'N/A' }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-[#176334]/70">Submitted</dt>
                                <dd class="font-medium text-right">{{ $application->submitted_at?->format('M d, Y h:i A') ?? 'N/A' }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="rounded-xl border border-[#176334]/15 p-4">
                        <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-[#176334]/70">Staff Remarks</p>
                        @if (filled($application->resubmission_remarks))
                            <p class="text-sm leading-relaxed text-[#176334]">{{ $application->resubmission_remarks }}</p>
                        @else
                            <p class="text-sm text-[#176334]/70">No additional remarks at this time.</p>
                        @endif
                    </div>
                </div>

                <div class="mt-6 rounded-xl border border-[#176334]/15 p-4 md:p-5">
                    <p class="mb-4 text-xs font-semibold uppercase tracking-wide text-[#176334]/70">Detailed History</p>

                    <div class="space-y-4">
                        @foreach ($detailedHistory as $history)
                            <div class="flex gap-3">
                                <div class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full {{ $history['type'] === 'review' ? 'bg-[#6C9C02]' : 'bg-[#2E7DFA]' }}"></div>
                                <div class="min-w-0 flex-1 border-b border-[#176334]/10 pb-3 last:border-b-0 last:pb-0">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <p class="text-sm font-semibold text-[#176334]">{{ $history['title'] }}</p>
                                        <p class="text-xs text-[#176334]/70">{{ $history['happened_at']->format('M d, Y h:i A') }}</p>
                                    </div>
                                    @if (filled($history['details']))
                                        <p class="mt-1 text-sm text-[#176334]/80">{{ $history['details'] }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            @if ($canResubmit)
                <section class="rounded-2xl border border-[#6C9C02]/30 bg-white p-6 shadow md:p-8">
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold text-[#176334]">Requested Document Resubmission</h3>
                        <p class="text-sm text-[#176334]/75">
                            Upload only the documents requested by staff below. Previously submitted files are not displayed for data protection.
                        </p>
                    </div>

                    @if ($errors->resubmit->any())
                        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            {{ $errors->resubmit->first() }}
                        </div>
                    @endif

                    <form action="{{ route('applicant.track.resubmit') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <input type="hidden" name="hp_token" value="">

                        @foreach ($requestedResubmissionSlots as $slotKey => $slot)
                            <div class="rounded-lg border border-[#176334]/15 p-4">
                                <label for="documents_{{ $slotKey }}" class="mb-2 block text-sm font-semibold text-[#176334]">
                                    {{ $slot['label'] }}
                                </label>
                                <input
                                    id="documents_{{ $slotKey }}"
                                    type="file"
                                    name="documents[{{ $slotKey }}]"
                                    accept=".jpg,.jpeg"
                                    class="block w-full cursor-pointer rounded-md border border-[#176334]/30 text-sm file:mr-4 file:cursor-pointer file:border-0 file:bg-[#176334] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:opacity-90"
                                    required
                                >
                                @error('documents.'.$slotKey, 'resubmit')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach

                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-lg bg-[#6C9C02] px-5 py-2.5 text-sm font-semibold text-white transition hover:opacity-90"
                        >
                            Submit Requested Documents
                        </button>
                    </form>
                </section>
            @endif
        @endif

        <div>
            <a href="{{ url('/') }}" class="inline-flex rounded-lg bg-[#176334] px-4 py-2 text-sm font-medium text-white hover:opacity-90 transition">
                Back to Directory
            </a>
        </div>
    </main>
</body>
</html>
