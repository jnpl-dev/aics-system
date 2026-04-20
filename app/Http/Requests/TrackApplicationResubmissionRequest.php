<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TrackApplicationResubmissionRequest extends FormRequest
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
            'hp_token' => ['honeypot'],
            'documents' => ['required', 'array', 'min:1'],
            'documents.*' => ['file', 'mimes:jpg,jpeg', 'max:1024'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'documents.required' => 'Please upload the requested documents.',
            'documents.*.mimes' => 'Requested files must be JPG or JPEG images only.',
            'documents.*.max' => 'Each requested file must not exceed 1MB.',
        ];
    }
}
