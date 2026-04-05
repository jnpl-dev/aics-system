<?php

namespace App\Filament\Resources\Applications\Pages;

use App\Filament\Resources\Applications\ApplicationResource;
use App\Models\Application;
use App\Models\ApplicationLog;
use App\Models\ApplicationReview;
use App\Models\Document;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ReviewApplication extends Page
{
    use InteractsWithRecord;

    protected static string $resource = ApplicationResource::class;

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.resources.applications.pages.review-application';

    public ?string $selectedDocumentUrl = null;

    public ?string $selectedDocumentName = null;

    public bool $isDocumentViewerOpen = false;

    public int $viewerZoom = 100;

    /**
     * @var list<string>|null
     */
    private ?array $applicationStatusEnumValuesCache = null;

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);

        abort_unless(static::getResource()::canAccess(), 403);
    }

    public function getTitle(): string
    {
        return 'Review Application: ' . (string) $this->getRecord()->reference_code;
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function forwardToMswdoAction(): Action
    {
        return Action::make('forwardToMswdo')
            ->label('Forward to MSWDO')
            ->icon('heroicon-o-paper-airplane')
            ->color('success')
            ->requiresConfirmation()
            ->action(function (): void {
                /** @var Application $record */
                $record = $this->getRecord();
                $fromStatus = (string) $record->status;
                $toStatus = $this->resolvePreferredStatus('forwarded_to_mswd', ['under_review']);

                if ($toStatus === null) {
                    Notification::make()
                        ->title('Unable to update application status')
                        ->body('No compatible status value exists in the current database enum.')
                        ->danger()
                        ->send();

                    return;
                }

                $updates = [
                    'status' => $toStatus,
                    'updated_at' => now(),
                ];

                if (Schema::hasColumn('application', 'reviewed_by')) {
                    $updates['reviewed_by'] = auth()->user()?->user_id;
                }

                if (Schema::hasColumn('application', 'reviewed_at')) {
                    $updates['reviewed_at'] = now();
                }

                $record->fill($updates);
                $record->save();

                $this->recordApplicationReview(
                    $record,
                    decision: 'approved',
                    remarks: 'Validated by AICS staff and forwarded to MSWDO.'
                );

                $this->recordApplicationLog(
                    $record,
                    action: 'forward_to_mswdo',
                    fromStatus: $fromStatus,
                    toStatus: $toStatus,
                    remarks: 'Application forwarded to MSWDO from review page.'
                );

                Notification::make()
                    ->title('Application forwarded to MSWDO')
                    ->success()
                    ->send();

                $this->redirect(ApplicationResource::getUrl('index'));
            });
    }

    protected function returnAndRequestResubmissionAction(): Action
    {
        return Action::make('returnAndRequestResubmission')
            ->label('Return & Request Resubmission')
            ->icon('heroicon-o-arrow-uturn-left')
            ->color('warning')
            ->modalHeading('Return application and request resubmission')
            ->modalSubmitActionLabel('Send resubmission request')
            ->schema([
                CheckboxList::make('document_ids')
                    ->label('Documents to be resubmitted')
                    ->options(fn (): array => $this->getDocumentCheckboxOptions())
                    ->required()
                    ->minItems(1)
                    ->columns(1),

                Textarea::make('remarks')
                    ->label('Remarks for applicant')
                    ->required()
                    ->minLength(10)
                    ->rows(4)
                    ->maxLength(2000),
            ])
            ->action(function (array $data): void {
                /** @var Application $record */
                $record = $this->getRecord();
                $fromStatus = (string) $record->status;
                $toStatus = $this->resolvePreferredStatus('pending_additional_docs', ['resubmission_required', 'rejected']);

                if ($toStatus === null) {
                    Notification::make()
                        ->title('Unable to update application status')
                        ->body('No compatible status value exists in the current database enum.')
                        ->danger()
                        ->send();

                    return;
                }

                $selectedDocumentIds = array_values(array_map('intval', (array) ($data['document_ids'] ?? [])));
                $remarks = trim((string) ($data['remarks'] ?? ''));

                $updates = [
                    'status' => $toStatus,
                    'updated_at' => now(),
                ];

                if (Schema::hasColumn('application', 'resubmission_document_ids')) {
                    $updates['resubmission_document_ids'] = $selectedDocumentIds;
                }

                if (Schema::hasColumn('application', 'resubmission_remarks')) {
                    $updates['resubmission_remarks'] = $remarks;
                }

                if (Schema::hasColumn('application', 'reviewed_by')) {
                    $updates['reviewed_by'] = auth()->user()?->user_id;
                }

                if (Schema::hasColumn('application', 'reviewed_at')) {
                    $updates['reviewed_at'] = now();
                }

                $record->fill($updates);
                $record->save();

                $this->recordApplicationReview(
                    $record,
                    decision: 'resubmission_required',
                    remarks: $remarks
                );

                $this->recordApplicationLog(
                    $record,
                    action: 'return_request_resubmission',
                    fromStatus: $fromStatus,
                    toStatus: $toStatus,
                    remarks: $this->buildResubmissionLogRemarks($remarks, $selectedDocumentIds)
                );

                Notification::make()
                    ->title('Resubmission request sent')
                    ->success()
                    ->send();

                $this->redirect(ApplicationResource::getUrl('index'));
            });
    }

    public function getDocumentsProperty(): Collection
    {
        return $this->getRecord()
            ->documents()
            ->with('requirement')
            ->orderByDesc('uploaded_at')
            ->get();
    }

    public function openDocument(int $documentId): void
    {
        /** @var Document|null $document */
        $document = $this->documents->firstWhere('document_id', $documentId);

        if (! $document) {
            return;
        }

        $this->selectedDocumentName = (string) ($document->file_name ?? 'Document');
        $this->selectedDocumentUrl = $this->resolveDocumentUrl((string) $document->file_path);
        $this->viewerZoom = 100;
        $this->isDocumentViewerOpen = $this->selectedDocumentUrl !== null;

        if ($this->selectedDocumentUrl === null) {
            Notification::make()
                ->title('Unable to open document preview')
                ->body('Please check storage configuration and try again.')
                ->warning()
                ->send();
        }
    }

    public function increaseZoom(): void
    {
        $this->viewerZoom = min($this->viewerZoom + 10, 200);
    }

    public function decreaseZoom(): void
    {
        $this->viewerZoom = max($this->viewerZoom - 10, 50);
    }

    public function resetZoom(): void
    {
        $this->viewerZoom = 100;
    }

    public function closeDocumentViewer(): void
    {
        $this->isDocumentViewerOpen = false;
        $this->selectedDocumentUrl = null;
        $this->selectedDocumentName = null;
        $this->viewerZoom = 100;
    }

    public function getSelectedDocumentEmbedUrlProperty(): ?string
    {
        if (! is_string($this->selectedDocumentUrl) || $this->selectedDocumentUrl === '') {
            return null;
        }

        return $this->selectedDocumentUrl . '#toolbar=1&navpanes=0&scrollbar=1&zoom=' . $this->viewerZoom;
    }

    /**
     * @return array<int, string>
     */
    private function getDocumentCheckboxOptions(): array
    {
        return $this->documents
            ->mapWithKeys(static function (Document $document): array {
                $label = (string) ($document->file_name ?? 'Document');
                $requirement = filled($document->requirement?->name)
                    ? (string) $document->requirement->name
                    : 'No requirement assigned';
                $uploadedAt = $document->uploaded_at?->format('M d, Y h:i A') ?? 'No timestamp';

                return [
                    (int) $document->document_id => sprintf('%s (Requirement: %s) · %s', $label, $requirement, $uploadedAt),
                ];
            })
            ->all();
    }

    public function getLatestReviewProperty(): ?ApplicationReview
    {
        if (! Schema::hasTable('application_review')) {
            return null;
        }

        return ApplicationReview::query()
            ->where('application_id', $this->getRecord()->application_id)
            ->orderByDesc('reviewed_at')
            ->orderByDesc('review_id')
            ->first();
    }

    public function getLatestApplicationLogProperty(): ?ApplicationLog
    {
        if (! Schema::hasTable('application_log')) {
            return null;
        }

        return ApplicationLog::query()
            ->where('application_id', $this->getRecord()->application_id)
            ->orderByDesc('timestamp')
            ->orderByDesc('log_id')
            ->first();
    }

    public function getReviewCountProperty(): int
    {
        if (! Schema::hasTable('application_review')) {
            return 0;
        }

        return ApplicationReview::query()
            ->where('application_id', $this->getRecord()->application_id)
            ->count();
    }

    public function getLogCountProperty(): int
    {
        if (! Schema::hasTable('application_log')) {
            return 0;
        }

        return ApplicationLog::query()
            ->where('application_id', $this->getRecord()->application_id)
            ->count();
    }

    private function resolveDocumentUrl(string $path): ?string
    {
        $path = trim($path);

        if ($path === '') {
            return null;
        }

        $diskName = (string) config('supabase.storage_disk', 'supabase');

        try {
            return Storage::disk($diskName)->temporaryUrl($path, now()->addMinutes(15));
        } catch (Throwable) {
            try {
                return Storage::disk($diskName)->url($path);
            } catch (Throwable) {
                return null;
            }
        }
    }

    /**
     * @param list<string> $fallbacks
     */
    private function resolvePreferredStatus(string $preferred, array $fallbacks = []): ?string
    {
        $allowed = $this->getApplicationStatusEnumValues();

        foreach ([$preferred, ...$fallbacks] as $candidate) {
            if (in_array($candidate, $allowed, true)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function getApplicationStatusEnumValues(): array
    {
        if (is_array($this->applicationStatusEnumValuesCache)) {
            return $this->applicationStatusEnumValuesCache;
        }

        try {
            $column = DB::selectOne("SHOW COLUMNS FROM `application` LIKE 'status'");
            $type = (string) ($column->Type ?? '');

            if (preg_match('/^enum\\((.*)\\)$/', $type, $matches) === 1) {
                /** @var list<string> $values */
                $values = array_values(array_filter(array_map(
                    static fn (string $value): string => trim($value, "' \t\n\r\0\x0B"),
                    str_getcsv($matches[1], ',', "'", '\\\\')
                )));

                $this->applicationStatusEnumValuesCache = $values;

                return $values;
            }
        } catch (Throwable) {
            // Fallback to known statuses when schema introspection fails.
        }

        $this->applicationStatusEnumValuesCache = [
            'submitted',
            'under_review',
            'forwarded_to_mswd',
            'pending_additional_docs',
            'approved_by_mswd',
            'coding',
            'forwarded_to_mayor',
            'approved_by_mayor',
            'voucher_preparation',
            'forwarded_to_accounting',
            'forwarded_to_treasury',
            'cheque_ready',
            'claimed',
            'on_hold',
            'rejected',
        ];

        return $this->applicationStatusEnumValuesCache;
    }

    private function recordApplicationReview(Application $application, string $decision, string $remarks): void
    {
        if (! Schema::hasTable('application_review')) {
            return;
        }

        $reviewColumns = Schema::getColumnListing('application_review');

        $payload = [
            'application_id' => $application->application_id,
            'reviewed_by' => auth()->user()?->user_id,
            'stage' => 'aics_validation',
            'decision' => $decision,
            'feedback_remarks' => $remarks,
            'reviewed_at' => now(),
        ];

        $payload = array_filter(
            $payload,
            static fn (mixed $value, string $column): bool => in_array($column, $reviewColumns, true),
            ARRAY_FILTER_USE_BOTH
        );

        if ($payload === []) {
            return;
        }

        ApplicationReview::query()->create($payload);
    }

    private function recordApplicationLog(Application $application, string $action, ?string $fromStatus, ?string $toStatus, ?string $remarks): void
    {
        if (! Schema::hasTable('application_log')) {
            return;
        }

        $logColumns = Schema::getColumnListing('application_log');

        $payload = [
            'application_id' => $application->application_id,
            'performed_by' => auth()->user()?->user_id,
            'action' => $action,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'remarks' => $remarks,
            'timestamp' => now(),
        ];

        $payload = array_filter(
            $payload,
            static fn (mixed $value, string $column): bool => in_array($column, $logColumns, true),
            ARRAY_FILTER_USE_BOTH
        );

        if ($payload === []) {
            return;
        }

        ApplicationLog::query()->create($payload);
    }

    /**
     * @param list<int> $documentIds
     */
    private function buildResubmissionLogRemarks(string $remarks, array $documentIds): string
    {
        $parts = [];

        if ($remarks !== '') {
            $parts[] = 'remarks=' . $remarks;
        }

        if ($documentIds !== []) {
            $parts[] = 'document_ids=' . json_encode($documentIds, JSON_UNESCAPED_UNICODE);
        }

        return $parts === []
            ? 'Resubmission requested.'
            : implode('; ', $parts);
    }
}
