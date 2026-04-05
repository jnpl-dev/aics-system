<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApplicantApplicationRequest;
use App\Models\Application;
use App\Models\AssistanceCategory;
use App\Models\Document;
use App\Models\Requirement;
use App\Services\ApplicantDocumentStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ApplicantApplicationController extends Controller
{
    /**
     * @var array<string, string>
     */
    private const REQUIREMENT_KEY_TO_NAME = [
        'medical_certificate' => 'Medical Certificate',
        'prescription' => 'Prescription',
        'applicant_government_id' => "Applicant's Government ID",
        'beneficiary_government_id' => "Beneficiary's Government ID",
        'applicant_cedula' => "Applicant's Cedula",
        'barangay_indigency' => 'Barangay Indigency',
        'authorization_letter' => 'Authorization Letter',
        'hospital_bill' => 'Hospital Bill',
        'medical_certificate_abstract' => 'Medical Certificate/Abstract',
        'certified_birth_certificate' => 'Certified Copy of Birth Certificate',
        'beneficiary_barangay_residency' => "Beneficiary's Barangay Residency",
    ];

    public function create()
    {
        return view('applicant.apply');
    }

    public function success(string $referenceCode)
    {
        $applicationExists = Application::query()
            ->where('reference_code', $referenceCode)
            ->exists();

        if (! $applicationExists) {
            return redirect()
                ->route('applicant.apply')
                ->withErrors(['submission' => 'Reference code not found. Please submit an application first.']);
        }

        return view('applicant.success', [
            'referenceCode' => $referenceCode,
        ]);
    }

    public function store(StoreApplicantApplicationRequest $request, ApplicantDocumentStorageService $documentStorage): RedirectResponse
    {
        $validated = $request->validated();

        $category = AssistanceCategory::query()
            ->where('name', (string) $validated['category_name'])
            ->where('is_active', true)
            ->first();

        if (! $category) {
            return redirect()
                ->route('applicant.apply')
                ->withErrors(['category_name' => 'Selected assistance category is not available.'])
                ->withInput();
        }

        $referenceCode = $this->generateReferenceCode();
        $uploadedPaths = [];

        try {
            DB::transaction(function () use ($validated, $category, $referenceCode, $documentStorage, &$uploadedPaths): void {
                $application = Application::query()->create([
                    'category_id' => (int) $category->category_id,
                    'submitted_by' => auth()->id(),
                    'reference_code' => $referenceCode,
                    'status' => 'submitted',
                    'applicant_last_name' => (string) $validated['applicant']['last_name'],
                    'applicant_first_name' => (string) $validated['applicant']['first_name'],
                    'applicant_middle_name' => (string) $validated['applicant']['middle_name'],
                    'applicant_sex' => strtolower((string) $validated['applicant']['sex']),
                    'applicant_dob' => (string) $validated['applicant']['date_of_birth'],
                    'applicant_address' => (string) $validated['applicant']['address'],
                    'applicant_phone' => (string) $validated['applicant']['phone_number'],
                    'applicant_relationship_to_beneficiary' => (string) $validated['beneficiary']['relationship'],
                    'beneficiary_last_name' => (string) $validated['beneficiary']['last_name'],
                    'beneficiary_first_name' => (string) $validated['beneficiary']['first_name'],
                    'beneficiary_middle_name' => (string) $validated['beneficiary']['middle_name'],
                    'beneficiary_sex' => strtolower((string) $validated['beneficiary']['sex']),
                    'beneficiary_dob' => (string) $validated['beneficiary']['date_of_birth'],
                    'beneficiary_address' => (string) $validated['beneficiary']['address'],
                    'submitted_at' => now(),
                    'updated_at' => now(),
                ]);

                $requirementsByName = Requirement::query()
                    ->where('category_id', (int) $category->category_id)
                    ->pluck('requirement_id', 'name');

                foreach ((array) ($validated['requirements'] ?? []) as $requirementKey => $file) {
                    if (! $file instanceof \Illuminate\Http\UploadedFile) {
                        continue;
                    }

                    $fileMeta = $documentStorage->storeRequirementDocument($file, $referenceCode, (string) $requirementKey);
                    $uploadedPaths[] = $fileMeta['path'];

                    $requirementName = self::REQUIREMENT_KEY_TO_NAME[$requirementKey] ?? null;
                    $requirementId = $requirementName ? ($requirementsByName[$requirementName] ?? null) : null;

                    Document::query()->create([
                        'application_id' => (int) $application->application_id,
                        'requirement_id' => $requirementId ? (int) $requirementId : null,
                        'uploaded_by' => auth()->id(),
                        'document_type' => $requirementKey === 'authorization_letter' ? 'authorization_letter' : 'supporting_document',
                        'file_name' => $fileMeta['file_name'],
                        'file_path' => $fileMeta['path'],
                        'file_size' => $fileMeta['file_size'],
                        'mime_type' => $fileMeta['mime_type'],
                        'uploaded_at' => now(),
                    ]);
                }
            });
        } catch (Throwable $exception) {
            if ($uploadedPaths !== []) {
                Storage::disk((string) config('supabase.storage_disk', 'supabase'))->delete($uploadedPaths);
            }

            report($exception);

            return redirect()
                ->route('applicant.apply')
                ->withErrors(['submission' => 'We could not save your application right now. Please try again.'])
                ->withInput();
        }

        return redirect()
            ->route('applicant.apply.success', ['referenceCode' => $referenceCode]);
    }

    private function generateReferenceCode(): string
    {
        do {
            $referenceCode = sprintf('AICS-%s-%06d', now()->format('Ymd'), random_int(0, 999999));
        } while (Application::query()->where('reference_code', $referenceCode)->exists());

        return $referenceCode;
    }
}
