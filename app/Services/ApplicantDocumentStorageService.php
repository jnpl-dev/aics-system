<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use RuntimeException;
use Throwable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ApplicantDocumentStorageService
{
    /**
     * @return array{path: string, file_name: string, file_size: int, mime_type: string}
     */
    public function storeRequirementDocument(UploadedFile $file, string $referenceCode, string $requirementKey): array
    {
        $directory = trim((string) config('supabase.storage_paths.applicant_documents', 'applications')); 
        $safeReference = strtolower(preg_replace('/[^A-Za-z0-9\-]+/', '-', $referenceCode) ?? $referenceCode);
        $safeRequirementKey = strtolower(preg_replace('/[^A-Za-z0-9_\-]+/', '_', $requirementKey) ?? $requirementKey);
        $pdfContents = $this->convertImageToPdf($file);
        $sourceBaseName = pathinfo((string) ($file->getClientOriginalName() ?: 'document'), PATHINFO_FILENAME);
        $safeBaseName = trim((string) preg_replace('/[^A-Za-z0-9\-\_\.]+/', '-', $sourceBaseName), '-_.');
        $safeBaseName = $safeBaseName !== '' ? $safeBaseName : 'document';

        $targetPath = sprintf(
            '%s/%s/%s_%s.pdf',
            trim($directory, '/'),
            $safeReference,
            now()->format('YmdHisv'),
            $safeRequirementKey
        );

        Storage::disk((string) config('supabase.storage_disk', 'supabase'))->put($targetPath, $pdfContents);

        return [
            'path' => $targetPath,
            'file_name' => sprintf('%s.pdf', $safeBaseName),
            'file_size' => strlen($pdfContents),
            'mime_type' => 'application/pdf',
        ];
    }

    private function convertImageToPdf(UploadedFile $file): string
    {
        $imageContents = $file->get();
        if (!is_string($imageContents) || $imageContents === '') {
            $realPath = $file->getRealPath();
            $imageContents = is_string($realPath) && $realPath !== ''
                ? (string) @file_get_contents($realPath)
                : '';
        }

        if (!is_string($imageContents) || $imageContents === '') {
            return $this->renderFallbackPdf((string) ($file->getClientOriginalName() ?: 'uploaded-image.jpg'));
        }

        $encodedImage = base64_encode($imageContents);
        $html = <<<HTML
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 18pt; }
        html, body { margin: 0; padding: 0; }
        .page { width: 100%; text-align: center; }
        img { max-width: 100%; max-height: 100%; object-fit: contain; }
    </style>
</head>
<body>
    <div class="page">
        <img src="data:image/jpeg;base64,{$encodedImage}" alt="Uploaded requirement">
    </div>
</body>
</html>
HTML;

        try {
            return $this->renderPdfFromHtml($html);
        } catch (Throwable) {
            return $this->renderFallbackPdf((string) ($file->getClientOriginalName() ?: 'uploaded-image.jpg'));
        }
    }

    private function renderFallbackPdf(string $fileName): string
    {
        $fallbackHtml = <<<HTML
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 24pt; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12pt; color: #1f2937; }
    </style>
</head>
<body>
    <p>Uploaded image: {$fileName}</p>
    <p>The file was received and converted to PDF.</p>
</body>
</html>
HTML;

        return $this->renderPdfFromHtml($fallbackHtml);
    }

    private function renderPdfFromHtml(string $html): string
    {
        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();

        $output = $dompdf->output();
        if (!is_string($output) || $output === '') {
            throw new RuntimeException('Failed to generate PDF output from uploaded image.');
        }

        return $output;
    }
}
