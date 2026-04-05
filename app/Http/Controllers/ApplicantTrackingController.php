<?php

namespace App\Http\Controllers;

use App\Http\Requests\TrackApplicationLookupRequest;
use App\Http\Requests\TrackApplicationResubmissionRequest;
use App\Models\Application;
use App\Models\ApplicationLog;
use App\Models\ApplicationReview;
use App\Models\Document;
use App\Services\ApplicantDocumentStorageService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Throwable;

class ApplicantTrackingController extends Controller
{
    private const TRACKING_SESSION_KEY = 'applicant_tracking_access';

    private const TRACKING_SESSION_TTL_MINUTES = 30;

    /**
     * @var array<string, string>
     */
    private const STATUS_LABELS = [
        'submitted' => 'Submitted',
        'resubmission_required' => 'Resubmission Required',
        'forwarded_to_mswdo' => 'Forwarded to MSWDO',
        'additional_docs_required' => 'Additional Documents Required',
        'pending_assistance_code' => 'Pending Assistance Code',
        'forwarded_to_mayors_office' => "Forwarded to Mayor's Office",
        'code_adjustment_required' => 'Code Adjustment Required',
        'pending_voucher' => 'Pending Voucher',
        'forwarded_to_accounting' => 'Forwarded to Accounting',
        'voucher_adjustment_required' => 'Voucher Adjustment Required',
        'pending_cheque' => 'Pending Cheque',
        'cheque_on_hold' => 'Cheque On Hold',
        'cheque_ready' => 'Cheque Prepared',
        'cheque_claimed' => 'Cheque Claimed',
        'claimed' => 'Cheque Claimed',
    ];

    public function index(): View
    {
        return view('applicant.track', [
            'application' => null,
            'timeline' => [],
            'detailedHistory' => [],
            'requestedResubmissionSlots' => [],
            'canResubmit' => false,
            'mainStageLabel' => null,
            'statusLabel' => null,
        ]);
    }

    public function access(TrackApplicationLookupRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $referenceCode = (string) $validated['reference_code'];
        $surname = (string) $validated['applicant_surname'];

        $application = $this->findApplicationByTrackingCredentials($referenceCode, $surname);

        if (! $application instanceof Application) {
            return back()
                ->withErrors([
                    'reference_code' => 'We could not find an application matching that reference number and surname.',
                ])
                ->withInput();
        }

        session()->put(self::TRACKING_SESSION_KEY, [
            'application_id' => (int) $application->application_id,
            'granted_at' => now()->toIso8601String(),
        ]);

        return redirect()->route('applicant.track.application');
    }

    public function show(): View|RedirectResponse
    {
        $application = $this->resolveTrackedApplicationFromSession();

        if (! $application instanceof Application) {
            return redirect()
                ->route('applicant.track')
                ->withErrors([
                    'reference_code' => 'Tracking access expired. Please enter your reference number and surname again.',
                ]);
        }

        $requestedResubmissionSlots = $this->buildRequestedResubmissionSlots($application);
        $currentStatus = (string) $application->status;

        return view('applicant.track', [
            'application' => $application,
            'timeline' => $this->buildTimeline($currentStatus),
            'detailedHistory' => $this->buildDetailedHistory($application),
            'requestedResubmissionSlots' => $requestedResubmissionSlots,
            'canResubmit' => $this->canResubmitDocuments($currentStatus, $requestedResubmissionSlots),
            'mainStageLabel' => $this->resolveMainStageLabel($currentStatus),
            'statusLabel' => $this->resolveStatusLabel($currentStatus),
        ]);
    }

    public function resubmit(TrackApplicationResubmissionRequest $request, ApplicantDocumentStorageService $documentStorage): RedirectResponse
    {
        $application = $this->resolveTrackedApplicationFromSession();

        if (! $application instanceof Application) {
            return redirect()
                ->route('applicant.track')
                ->withErrors([
                    'reference_code' => 'Tracking access expired. Please enter your reference number and surname again.',
                ]);
        }

        $requestedResubmissionSlots = $this->buildRequestedResubmissionSlots($application);
        $currentStatus = (string) $application->status;
        $nextStatus = $this->resolvePreferredStatus('submitted');

        if (! $this->canResubmitDocuments($currentStatus, $requestedResubmissionSlots)) {
            return redirect()
                ->route('applicant.track.application')
                ->withErrors([
                    'documents' => 'This application currently has no requested documents for resubmission.',
                ], 'resubmit');
        }

        if ($nextStatus === null) {
            return redirect()
                ->route('applicant.track.application')
                ->withErrors([
                    'documents' => 'Unable to update application status to submitted after resubmission.',
                ], 'resubmit');
        }

        /** @var array<string, UploadedFile> $uploadedDocuments */
        $uploadedDocuments = (array) $request->file('documents', []);

        $expectedKeys = array_keys($requestedResubmissionSlots);
        $providedKeys = array_keys($uploadedDocuments);

        $missingKeys = array_values(array_diff($expectedKeys, $providedKeys));
        $unexpectedKeys = array_values(array_diff($providedKeys, $expectedKeys));

        if ($missingKeys !== [] || $unexpectedKeys !== []) {
            $errors = [];

            foreach ($missingKeys as $missingKey) {
                $label = (string) ($requestedResubmissionSlots[$missingKey]['label'] ?? 'Requested document');
                $errors['documents.' . $missingKey] = "Please upload {$label}.";
            }

            if ($unexpectedKeys !== []) {
                $errors['documents'] = 'One or more uploaded files were not requested by staff.';
            }

            return redirect()
                ->route('applicant.track.application')
                ->withErrors($errors, 'resubmit');
        }

        $uploadedPaths = [];
        $oldPathsToDelete = [];

        try {
            DB::transaction(function () use ($application, $uploadedDocuments, $requestedResubmissionSlots, $documentStorage, $nextStatus, &$uploadedPaths, &$oldPathsToDelete): void {
                foreach ($requestedResubmissionSlots as $slotKey => $slot) {
                    $file = $uploadedDocuments[$slotKey] ?? null;

                    if (! $file instanceof UploadedFile) {
                        continue;
                    }

                    $requirementKey = (string) ($slot['requirement_key'] ?? 'requested_document');
                    $fileMeta = $documentStorage->storeRequirementDocument($file, (string) $application->reference_code, $requirementKey);
                    $uploadedPaths[] = $fileMeta['path'];

                    $existingDocumentId = isset($slot['document_id']) ? (int) $slot['document_id'] : 0;
                    $existingDocumentPath = trim((string) ($slot['existing_file_path'] ?? ''));

                    $label = (string) ($slot['label'] ?? 'Requested Document');
                    $isAuthorizationLetter = Str::contains(strtolower($label), 'authorization letter');

                    $payload = [
                        'application_id' => (int) $application->application_id,
                        'requirement_id' => $slot['requirement_id'] ?? null,
                        'uploaded_by' => null,
                        'document_type' => $isAuthorizationLetter ? 'authorization_letter' : 'supporting_document',
                        'file_name' => $fileMeta['file_name'],
                        'file_path' => $fileMeta['path'],
                        'file_size' => $fileMeta['file_size'],
                        'mime_type' => $fileMeta['mime_type'],
                        'uploaded_at' => now(),
                    ];

                    $existingDocument = $existingDocumentId > 0
                        ? Document::query()
                            ->where('application_id', (int) $application->application_id)
                            ->where('document_id', $existingDocumentId)
                            ->first()
                        : null;

                    if ($existingDocument instanceof Document) {
                        $existingDocument->fill($payload);
                        $existingDocument->save();
                    } else {
                        Document::query()->create($payload);
                    }

                    if ($existingDocumentPath !== '' && $existingDocumentPath !== $fileMeta['path']) {
                        $oldPathsToDelete[] = $existingDocumentPath;
                    }
                }

                if ($oldPathsToDelete !== []) {
                    Storage::disk((string) config('supabase.storage_disk', 'supabase'))
                        ->delete(array_values(array_unique($oldPathsToDelete)));
                }

                $fromStatus = (string) $application->status;
                $updates = [
                    'status' => $nextStatus,
                    'updated_at' => now(),
                ];

                if (Schema::hasColumn('application', 'resubmission_document_ids')) {
                    $updates['resubmission_document_ids'] = null;
                }

                if (Schema::hasColumn('application', 'resubmission_remarks')) {
                    $updates['resubmission_remarks'] = null;
                }

                $application->fill($updates);
                $application->save();

                if (Schema::hasTable('application_log')) {
                    ApplicationLog::query()->create([
                        'application_id' => (int) $application->application_id,
                        'performed_by' => null,
                        'action' => 'applicant_resubmission_uploaded',
                        'decision' => $application->status,
                        'from_status' => $fromStatus,
                        'to_status' => $application->status,
                        'remarks' => 'Applicant uploaded requested resubmission documents from public tracking portal.',
                        'timestamp' => now(),
                    ]);
                }
            });
        } catch (Throwable $exception) {
            if ($uploadedPaths !== []) {
                Storage::disk((string) config('supabase.storage_disk', 'supabase'))->delete($uploadedPaths);
            }

            report($exception);

            return redirect()
                ->route('applicant.track.application')
                ->withErrors([
                    'documents' => 'We could not upload your resubmission files right now. Please try again.',
                ], 'resubmit');
        }

        return redirect()
            ->route('applicant.track.application')
            ->with('status', 'Requested documents were resubmitted successfully.');
    }

    private function findApplicationByTrackingCredentials(string $referenceCode, string $surname): ?Application
    {
        $normalizedReference = strtoupper(trim($referenceCode));
        $normalizedSurname = strtolower(trim($surname));

        if ($normalizedReference === '' || $normalizedSurname === '') {
            return null;
        }

        return Application::query()
            ->whereRaw('UPPER(reference_code) = ?', [$normalizedReference])
            ->whereRaw('LOWER(applicant_last_name) = ?', [$normalizedSurname])
            ->with('category')
            ->first();
    }

    private function resolveTrackedApplicationFromSession(): ?Application
    {
        $sessionPayload = session(self::TRACKING_SESSION_KEY);

        if (! is_array($sessionPayload)) {
            return null;
        }

        $grantedAt = (string) ($sessionPayload['granted_at'] ?? '');
        $applicationId = (int) ($sessionPayload['application_id'] ?? 0);

        if ($applicationId <= 0 || blank($grantedAt)) {
            return null;
        }

        $grantedAtMoment = Carbon::parse($grantedAt);

        if ($grantedAtMoment->lt(now()->subMinutes(self::TRACKING_SESSION_TTL_MINUTES))) {
            session()->forget(self::TRACKING_SESSION_KEY);

            return null;
        }

        return Application::query()
            ->with('category')
            ->find($applicationId);
    }

    /**
     * @return array<string, array{label: string, requirement_id: int|null, requirement_key: string, document_id: int, existing_file_path: string}>
     */
    private function buildRequestedResubmissionSlots(Application $application): array
    {
        if (! Schema::hasColumn('application', 'resubmission_document_ids')) {
            return [];
        }

        $requestedDocumentIds = collect((array) ($application->resubmission_document_ids ?? []))
            ->map(static fn (mixed $value): int => (int) $value)
            ->filter(static fn (int $value): bool => $value > 0)
            ->unique()
            ->values();

        if ($requestedDocumentIds->isEmpty()) {
            return [];
        }

        /** @var \Illuminate\Support\Collection<int, Document> $documents */
        $documents = Document::query()
            ->where('application_id', (int) $application->application_id)
            ->whereIn('document_id', $requestedDocumentIds->all())
            ->with('requirement')
            ->get();

        $slots = [];

        foreach ($documents as $document) {
            $requirementId = $document->requirement_id !== null
                ? (int) $document->requirement_id
                : null;

            $slotKey = $requirementId !== null
                ? 'req_' . $requirementId
                : 'doc_' . (int) $document->document_id;

            if (array_key_exists($slotKey, $slots)) {
                continue;
            }

            $label = filled($document->requirement?->name)
                ? (string) $document->requirement->name
                : ((string) ($document->file_name ?? 'Requested Document'));

            $slots[$slotKey] = [
                'label' => $label,
                'requirement_id' => $requirementId,
                'requirement_key' => Str::of($label)->lower()->slug('_')->toString(),
                'document_id' => (int) $document->document_id,
                'existing_file_path' => (string) ($document->file_path ?? ''),
            ];
        }

        return $slots;
    }

    /**
     * @param  array<string, array{label: string, requirement_id: int|null, requirement_key: string}>  $requestedResubmissionSlots
     */
    private function canResubmitDocuments(string $status, array $requestedResubmissionSlots): bool
    {
        if ($requestedResubmissionSlots === []) {
            return false;
        }

        return $status === 'resubmission_required';
    }

    /**
     * @return array<int, array{label: string, state: string}>
     */
    private function buildTimeline(string $status): array
    {
        $stages = [
            ['label' => 'Pending'],
            ['label' => 'In Process'],
            ['label' => 'Cheque Prepared'],
            ['label' => 'Cheque Claimed'],
        ];

        $currentStageIndex = match ($status) {
            'cheque_claimed',
            'claimed' => 3,
            'cheque_ready' => 2,
            'forwarded_to_mswdo',
            'additional_docs_required',
            'pending_assistance_code',
            'forwarded_to_mayors_office',
            'code_adjustment_required',
            'pending_voucher',
            'forwarded_to_accounting',
            'voucher_adjustment_required',
            'pending_cheque',
            'cheque_on_hold' => 1,
            default => 0,
        };

        return collect($stages)
            ->map(function (array $stage, int $index) use ($currentStageIndex): array {
                $state = 'upcoming';

                if ($index < $currentStageIndex) {
                    $state = 'completed';
                }

                if ($index === $currentStageIndex) {
                    $state = 'current';
                }

                return [
                    'label' => $stage['label'],
                    'state' => $state,
                ];
            })
            ->all();
    }

    private function resolveMainStageLabel(string $status): string
    {
        return match ($status) {
            'cheque_claimed',
            'claimed' => 'Cheque Claimed',
            'cheque_ready' => 'Cheque Prepared',
            'forwarded_to_mswdo',
            'additional_docs_required',
            'pending_assistance_code',
            'forwarded_to_mayors_office',
            'code_adjustment_required',
            'pending_voucher',
            'forwarded_to_accounting',
            'voucher_adjustment_required',
            'pending_cheque',
            'cheque_on_hold' => 'In Process',
            default => 'Pending',
        };
    }

    /**
     * @return array<int, array{type: string, title: string, details: string|null, happened_at: Carbon}>
     */
    private function buildDetailedHistory(Application $application): array
    {
        /** @var Collection<int, array{type: string, title: string, details: string|null, happened_at: Carbon}> $entries */
        $entries = collect();

        $submittedAt = $application->submitted_at instanceof Carbon
            ? $application->submitted_at
            : Carbon::parse((string) $application->submitted_at);

        $entries->push([
            'type' => 'status_log',
            'title' => 'Submitted',
            'details' => 'Application submitted and to be reviewed.',
            'happened_at' => $submittedAt,
        ]);

        if (Schema::hasTable('application_log')) {
            /** @var Collection<int, ApplicationLog> $logs */
            $logs = ApplicationLog::query()
                ->where('application_id', (int) $application->application_id)
                ->orderByDesc('timestamp')
                ->orderByDesc('log_id')
                ->get();

            $entries = $entries->merge(
                $logs
                    ->filter(static fn (ApplicationLog $log): bool => $log->timestamp !== null)
                    ->filter(static fn (ApplicationLog $log): bool => (string) $log->action === 'applicant_resubmission_uploaded')
                    ->map(static function (ApplicationLog $log): array {
                        return [
                            'type' => 'status_log',
                            'title' => 'Submitted',
                            'details' => filled($log->remarks)
                                ? (string) $log->remarks
                                : 'Applicant successfully resubmitted requested documents.',
                            'happened_at' => Carbon::parse($log->timestamp),
                        ];
                    })
            );
        }

        if (Schema::hasTable('application_review')) {
            /** @var Collection<int, ApplicationReview> $reviews */
            $reviews = ApplicationReview::query()
                ->where('application_id', (int) $application->application_id)
                ->orderByDesc('reviewed_at')
                ->orderByDesc('review_id')
                ->get();

            $entries = $entries->merge(
                $reviews
                    ->filter(static fn (ApplicationReview $review): bool => $review->reviewed_at !== null)
                    ->map(static function (ApplicationReview $review): array {
                        $stage = Str::of((string) ($review->stage ?? 'review'))
                            ->replace('_', ' ')
                            ->headline()
                            ->toString();

                        $decision = Str::of((string) ($review->decision ?? 'logged'))
                            ->replace('_', ' ')
                            ->headline()
                            ->toString();

                        return [
                            'type' => 'review',
                            'title' => sprintf('%s (%s)', $stage, $decision),
                            'details' => filled($review->feedback_remarks) ? (string) $review->feedback_remarks : null,
                            'happened_at' => Carbon::parse($review->reviewed_at),
                        ];
                    })
            );
        }

        return $entries
            ->sortByDesc(static fn (array $entry): int => $entry['happened_at']->getTimestamp())
            ->values()
            ->all();
    }

    private function resolveStatusLabel(string $status): string
    {
        return self::STATUS_LABELS[$status] ?? Str::of($status)->replace('_', ' ')->headline()->toString();
    }

    private function resolvePreferredStatus(string $preferred, array $fallbacks = []): ?string
    {
        $candidates = array_values(array_filter([$preferred, ...$fallbacks], static fn (string $value): bool => trim($value) !== ''));

        if ($candidates === []) {
            return null;
        }

        if (! Schema::hasTable('application')) {
            return $candidates[0];
        }

        if (! Schema::hasColumn('application', 'status')) {
            return $candidates[0];
        }

        $databaseDriver = (string) DB::connection()->getDriverName();

        if ($databaseDriver !== 'mysql') {
            return $candidates[0];
        }

        try {
            $column = DB::selectOne('SHOW COLUMNS FROM application WHERE Field = ?', ['status']);
            $columnType = (string) ($column->Type ?? '');

            preg_match_all("/'([^']+)'/", $columnType, $matches);
            $enumValues = array_map('strtolower', $matches[1] ?? []);

            if ($enumValues === []) {
                return $candidates[0];
            }

            foreach ($candidates as $candidate) {
                if (in_array(strtolower($candidate), $enumValues, true)) {
                    return $candidate;
                }
            }

            return null;
        } catch (Throwable) {
            return $candidates[0];
        }
    }
}
