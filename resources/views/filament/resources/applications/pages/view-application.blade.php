@php
    $record = $this->getRecord();

    $statusLabel = match ((string) $record->status) {
        'pending_additional_docs' => 'Pending Resubmission of Docs',
        default => str((string) $record->status)->replace('_', ' ')->title()->toString(),
    };

    $applicantName = trim(implode(' ', array_filter([
        (string) $record->applicant_first_name,
        (string) $record->applicant_middle_name,
        (string) $record->applicant_last_name,
    ])));

    $beneficiaryName = trim(implode(' ', array_filter([
        (string) $record->beneficiary_first_name,
        (string) $record->beneficiary_middle_name,
        (string) $record->beneficiary_last_name,
    ])));

    $latestReview = $this->latestReview;
    $latestLog = $this->latestApplicationLog;

    $decisionTone = match ((string) ($latestReview?->decision ?? '')) {
        'approved' => 'aics-pill--approved',
        'returned_for_resubmission', 'resubmission_required' => 'aics-pill--warning',
        default => 'aics-pill--neutral',
    };
@endphp

<x-filament-panels::page>
    <div class="mx-auto w-full max-w-7xl space-y-4">
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-4">
            <div class="xl:col-span-3 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900/40">
                <section class="border-b border-gray-200 p-6 dark:border-gray-800">
                    <h3 class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-700 dark:text-emerald-300">Application Profile (View Only)</h3>
                    <p class="mt-1 text-sm text-gray-500">This information is shown exactly as submitted and cannot be edited here.</p>

                    <div class="mt-4 space-y-3">
                        <div class="grid grid-cols-1 gap-2 md:grid-cols-12 md:items-center">
                            <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 md:col-span-4">Reference Code</label>
                            <input type="text" value="{{ (string) $record->reference_code }}" disabled class="md:col-span-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" />
                        </div>
                        <div class="grid grid-cols-1 gap-2 md:grid-cols-12 md:items-center">
                            <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 md:col-span-4">Status</label>
                            <input type="text" value="{{ $statusLabel }}" disabled class="md:col-span-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" />
                        </div>
                        <div class="grid grid-cols-1 gap-2 md:grid-cols-12 md:items-center">
                            <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 md:col-span-4">Submitted At</label>
                            <input type="text" value="{{ $record->submitted_at?->format('M d, Y h:i A') ?? 'N/A' }}" disabled class="md:col-span-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" />
                        </div>
                    </div>
                </section>

                <section class="border-b border-gray-200 p-6 dark:border-gray-800">
                    <h3 class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-700 dark:text-emerald-300">Personal Information</h3>

                    <div class="mt-4 space-y-3">
                        <div class="grid grid-cols-1 gap-2 md:grid-cols-12 md:items-center">
                            <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 md:col-span-4">Applicant Name</label>
                            <input type="text" value="{{ $applicantName !== '' ? $applicantName : 'N/A' }}" disabled class="md:col-span-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" />
                        </div>
                        <div class="grid grid-cols-1 gap-2 md:grid-cols-12 md:items-center">
                            <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 md:col-span-4">Applicant Sex</label>
                            <input type="text" value="{{ filled($record->applicant_sex) ? str((string) $record->applicant_sex)->title() : 'N/A' }}" disabled class="md:col-span-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" />
                        </div>
                        <div class="grid grid-cols-1 gap-2 md:grid-cols-12 md:items-center">
                            <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 md:col-span-4">Applicant Date of Birth</label>
                            <input type="text" value="{{ $record->applicant_dob?->format('M d, Y') ?? 'N/A' }}" disabled class="md:col-span-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" />
                        </div>
                        <div class="grid grid-cols-1 gap-2 md:grid-cols-12 md:items-center">
                            <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 md:col-span-4">Applicant Phone</label>
                            <input type="text" value="{{ (string) ($record->applicant_phone ?? 'N/A') }}" disabled class="md:col-span-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" />
                        </div>
                        <div class="grid grid-cols-1 gap-2 md:grid-cols-12 md:items-start">
                            <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 md:col-span-4 md:pt-2">Applicant Address</label>
                            <textarea disabled rows="2" class="md:col-span-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">{{ (string) ($record->applicant_address ?? 'N/A') }}</textarea>
                        </div>
                        <div class="grid grid-cols-1 gap-2 md:grid-cols-12 md:items-center">
                            <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 md:col-span-4">Relationship to Beneficiary</label>
                            <input type="text" value="{{ (string) ($record->applicant_relationship_to_beneficiary ?? 'N/A') }}" disabled class="md:col-span-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" />
                        </div>

                        <div class="grid grid-cols-1 gap-2 md:grid-cols-12 md:items-center">
                            <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 md:col-span-4">Beneficiary Name</label>
                            <input type="text" value="{{ $beneficiaryName !== '' ? $beneficiaryName : 'N/A' }}" disabled class="md:col-span-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" />
                        </div>
                        <div class="grid grid-cols-1 gap-2 md:grid-cols-12 md:items-center">
                            <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 md:col-span-4">Beneficiary Sex</label>
                            <input type="text" value="{{ filled($record->beneficiary_sex) ? str((string) $record->beneficiary_sex)->title() : 'N/A' }}" disabled class="md:col-span-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" />
                        </div>
                        <div class="grid grid-cols-1 gap-2 md:grid-cols-12 md:items-center">
                            <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 md:col-span-4">Beneficiary Date of Birth</label>
                            <input type="text" value="{{ $record->beneficiary_dob?->format('M d, Y') ?? 'N/A' }}" disabled class="md:col-span-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" />
                        </div>
                        <div class="grid grid-cols-1 gap-2 md:grid-cols-12 md:items-start">
                            <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 md:col-span-4 md:pt-2">Beneficiary Address</label>
                            <textarea disabled rows="2" class="md:col-span-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">{{ (string) ($record->beneficiary_address ?? 'N/A') }}</textarea>
                        </div>

                        @if (filled($record->resubmission_remarks))
                            <div class="grid grid-cols-1 gap-2 md:grid-cols-12 md:items-start">
                                <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 md:col-span-4 md:pt-2">Resubmission Remarks</label>
                                <textarea disabled rows="3" class="md:col-span-8 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">{{ (string) $record->resubmission_remarks }}</textarea>
                            </div>
                        @endif
                    </div>
                </section>

                <section class="p-6">
                    <h3 class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-700 dark:text-emerald-300">Documents</h3>
                    <p class="mt-1 text-sm text-gray-500">Each entry shows requirement and document type.</p>

                    <div class="mt-4 space-y-2">
                        @forelse($this->documents as $document)
                            @php
                                $requirementLabel = filled($document->requirement?->name) ? (string) $document->requirement->name : 'General Supporting Document';
                                $docTypeLabel = str((string) ($document->document_type ?? 'supporting_document'))->replace('_', ' ')->title();
                            @endphp
                            <div class="grid grid-cols-1 gap-3 rounded-xl border border-gray-200 bg-white p-3 sm:grid-cols-12 sm:items-center dark:border-gray-700 dark:bg-gray-900/30">
                                <div class="sm:col-span-9">
                                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $document->file_name }}</p>
                                    <p class="text-xs text-gray-500">Requirement: {{ $requirementLabel }}</p>
                                    <p class="text-xs text-gray-500">Type: {{ $docTypeLabel }} · {{ $document->uploaded_at?->format('M d, Y h:i A') ?? 'No timestamp' }}</p>
                                </div>
                                <div class="sm:col-span-3 sm:text-right">
                                    <button type="button" wire:click="openDocument({{ (int) $document->document_id }})" class="inline-flex rounded-lg border border-emerald-600 px-3 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-50 dark:border-emerald-500 dark:text-emerald-300 dark:hover:bg-emerald-900/30">View PDF</button>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No documents were uploaded for this application.</p>
                        @endforelse
                    </div>
                </section>
            </div>

            <aside class="xl:col-span-1 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900/40">
                <section class="p-6">
                    <h3 class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-700 dark:text-emerald-300">Review Trail</h3>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200">{{ $this->reviewCount }} review{{ $this->reviewCount === 1 ? '' : 's' }}</span>
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-200">{{ $this->logCount }} log entr{{ $this->logCount === 1 ? 'y' : 'ies' }}</span>
                    </div>

                    <div class="mt-4 space-y-4 text-sm text-gray-600 dark:text-gray-300">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Latest Decision</p>
                            @if ($latestReview)
                                <div class="mt-2">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $decisionTone === 'aics-pill--approved' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200' : ($decisionTone === 'aics-pill--warning' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200') }}">
                                        {{ str((string) $latestReview->decision)->replace('_', ' ')->title() }}
                                    </span>
                                    <p class="mt-1 text-xs text-gray-500">{{ $latestReview->reviewed_at?->format('M d, Y h:i A') ?? 'No timestamp' }}</p>
                                </div>
                            @else
                                <p class="mt-1 text-xs text-gray-500">No review decision recorded yet.</p>
                            @endif
                        </div>

                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Latest Activity</p>
                            @if ($latestLog)
                                <p class="mt-2 text-sm">
                                    {{ str((string) $latestLog->action)->replace('_', ' ')->title() }}
                                    @if (filled($latestLog->status_from) || filled($latestLog->status_to))
                                        · {{ str((string) ($latestLog->status_from ?? 'n/a'))->replace('_', ' ')->title() }} → {{ str((string) ($latestLog->status_to ?? 'n/a'))->replace('_', ' ')->title() }}
                                    @endif
                                </p>
                                <p class="mt-1 text-xs text-gray-500">{{ $latestLog->timestamp?->format('M d, Y h:i A') ?? 'No timestamp' }}</p>
                            @else
                                <p class="mt-1 text-xs text-gray-500">No activity log recorded yet.</p>
                            @endif
                        </div>
                    </div>
                </section>
            </aside>
        </div>

        <div class="flex items-center justify-start">
            <a href="{{ \App\Filament\Resources\Applications\ApplicationResource::getUrl('index') }}" class="inline-flex rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800">Cancel</a>
        </div>

        @if ($this->isDocumentViewerOpen && $this->selectedDocumentEmbedUrl)
            <div class="fixed inset-0 z-[1200] flex items-center justify-center bg-gray-900/80 p-4" wire:keydown.escape.window="closeDocumentViewer">
                <div class="flex h-[94vh] w-[98vw] max-w-[1600px] flex-col overflow-hidden rounded-xl border border-gray-300 bg-white shadow-2xl">
                    <div class="flex items-center justify-between gap-3 border-b border-gray-200 bg-gray-50 px-4 py-2">
                        <p class="truncate text-sm font-semibold text-gray-800">{{ $this->selectedDocumentName }} ({{ $this->viewerZoom }}%)</p>
                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="decreaseZoom" class="rounded-lg border border-gray-300 px-2.5 py-1 text-xs font-semibold text-gray-700 hover:bg-white">-</button>
                            <button type="button" wire:click="increaseZoom" class="rounded-lg border border-gray-300 px-2.5 py-1 text-xs font-semibold text-gray-700 hover:bg-white">+</button>
                            <button type="button" wire:click="resetZoom" class="rounded-lg border border-gray-300 px-2.5 py-1 text-xs font-semibold text-gray-700 hover:bg-white">Reset</button>
                            <button type="button" wire:click="closeDocumentViewer" class="rounded-lg border border-emerald-600 px-3 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-50">Close</button>
                        </div>
                    </div>
                    <iframe src="{{ $this->selectedDocumentEmbedUrl }}" class="h-full w-full bg-white" loading="lazy"></iframe>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
