<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreApplicantApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $allowedCategories = [
            'Medical Assistance',
            'Hospital Assistance',
            'Burial Assistance',
        ];

        $allowedSexes = ['Male', 'Female'];
        $allowedRelationships = ['Self', 'Parent', 'Sibling', 'Spouse', 'Child', 'Representative'];

    $documentRule = ['file', 'mimes:jpg,jpeg', 'max:1024'];

        return [
            'category_name' => ['required', Rule::in($allowedCategories)],

            'applicant.last_name' => ['required', 'string', 'max:120'],
            'applicant.first_name' => ['required', 'string', 'max:120'],
            'applicant.middle_name' => ['nullable', 'string', 'max:120'],
            'applicant.sex' => ['required', Rule::in($allowedSexes)],
            'applicant.date_of_birth' => ['required', 'date', 'before:today'],
            'applicant.phone_number' => ['required', 'regex:/^09\d{9}$/'],
            'applicant.address' => ['required', 'string', 'max:500'],

            'beneficiary.last_name' => ['required', 'string', 'max:120'],
            'beneficiary.first_name' => ['required', 'string', 'max:120'],
            'beneficiary.middle_name' => ['nullable', 'string', 'max:120'],
            'beneficiary.sex' => ['required', Rule::in($allowedSexes)],
            'beneficiary.date_of_birth' => ['required', 'date', 'before:today'],
            'beneficiary.relationship' => ['required', Rule::in($allowedRelationships)],
            'beneficiary.address' => ['required', 'string', 'max:500'],

            'requirements.medical_certificate' => [Rule::requiredIf($this->input('category_name') === 'Medical Assistance'), ...$documentRule],
            'requirements.hospital_bill' => [Rule::requiredIf($this->input('category_name') === 'Hospital Assistance'), ...$documentRule],
            'requirements.certified_birth_certificate' => [Rule::requiredIf($this->input('category_name') === 'Burial Assistance'), ...$documentRule],
            'requirements.prescription' => [Rule::requiredIf(in_array($this->input('category_name'), ['Medical Assistance', 'Hospital Assistance'], true)), ...$documentRule],
            'requirements.medical_certificate_abstract' => [Rule::requiredIf($this->input('category_name') === 'Hospital Assistance'), ...$documentRule],
            'requirements.applicant_government_id' => ['required', ...$documentRule],
            'requirements.beneficiary_government_id' => [Rule::requiredIf(in_array($this->input('category_name'), ['Medical Assistance', 'Hospital Assistance'], true)), ...$documentRule],
            'requirements.applicant_cedula' => ['required', ...$documentRule],
            'requirements.beneficiary_barangay_residency' => [Rule::requiredIf($this->input('category_name') === 'Burial Assistance'), ...$documentRule],
            'requirements.barangay_indigency' => ['required', ...$documentRule],
            'requirements.authorization_letter' => [Rule::requiredIf($this->input('beneficiary.relationship') === 'Representative'), ...$documentRule],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'applicant.phone_number.regex' => 'Applicant phone number must be a valid Philippine mobile number (09XXXXXXXXX).',
            'requirements.*.mimes' => 'Uploaded documents must be JPG or JPEG images only.',
            'requirements.*.max' => 'Each uploaded document must not exceed 1MB.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $clean = static function (?string $value): ?string {
            if ($value === null) {
                return null;
            }

            $trimmed = trim(strip_tags($value));
            return preg_replace('/\s+/', ' ', $trimmed);
        };

        $normalizedPhone = preg_replace('/\D+/', '', (string) $this->input('applicant.phone_number', ''));

        $this->merge([
            'category_name' => $clean($this->input('category_name')),
            'applicant' => [
                'last_name' => $clean($this->input('applicant.last_name')),
                'first_name' => $clean($this->input('applicant.first_name')),
                'middle_name' => $clean($this->input('applicant.middle_name')),
                'sex' => $clean($this->input('applicant.sex')),
                'date_of_birth' => $this->input('applicant.date_of_birth'),
                'phone_number' => $normalizedPhone,
                'address' => $clean($this->input('applicant.address')),
            ],
            'beneficiary' => [
                'last_name' => $clean($this->input('beneficiary.last_name')),
                'first_name' => $clean($this->input('beneficiary.first_name')),
                'middle_name' => $clean($this->input('beneficiary.middle_name')),
                'sex' => $clean($this->input('beneficiary.sex')),
                'date_of_birth' => $this->input('beneficiary.date_of_birth'),
                'relationship' => $clean($this->input('beneficiary.relationship')),
                'address' => $clean($this->input('beneficiary.address')),
            ],
        ]);
    }
}
