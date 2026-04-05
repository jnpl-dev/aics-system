<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TrackApplicationLookupRequest extends FormRequest
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
        return [
            'reference_code' => ['required', 'string', 'max:80'],
            'applicant_surname' => ['required', 'string', 'max:120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reference_code.required' => 'Please enter your reference number.',
            'applicant_surname.required' => 'Please enter the applicant surname.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $clean = static function (?string $value): string {
            $trimmed = trim((string) $value);
            $withoutTags = strip_tags($trimmed);

            return (string) preg_replace('/\s+/', ' ', $withoutTags);
        };

        $referenceCode = strtoupper($clean($this->input('reference_code')));

        $this->merge([
            'reference_code' => $referenceCode,
            'applicant_surname' => $clean($this->input('applicant_surname')),
        ]);
    }
}
