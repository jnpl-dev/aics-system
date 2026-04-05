<?php

namespace App\Filament\Resources\Applications\Pages;

use App\Filament\Resources\Applications\ApplicationResource;
use App\Models\ApplicationLog;
use App\Models\ApplicationReview;
use App\Models\Document;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ViewApplication extends Page
{
    use InteractsWithRecord;

    protected static string $resource = ApplicationResource::class;

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.resources.applications.pages.view-application';

    public ?string $selectedDocumentUrl = null;

    public ?string $selectedDocumentName = null;

    public bool $isDocumentViewerOpen = false;

    public int $viewerZoom = 100;

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);

        abort_unless(static::getResource()::canAccess(), 403);
    }

    public function getTitle(): string
    {
        return 'View Application: ' . (string) $this->getRecord()->reference_code;
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
}
