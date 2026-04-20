<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Applicant Apply | {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        input[type="file"]::file-selector-button {
            background-color: #176334 !important;
            color: #ffffff !important;
        }
    </style>
</head>
<body class="min-h-screen bg-[#FFFDFF] text-[#176334]">
    <main class="mx-auto max-w-5xl p-6 lg:p-10 space-y-6">
    <x-forms.page-feedback />

    <header class="rounded-xl border border-[#176334]/20 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">AICS Application</h1>
                    <p class="mt-1 text-sm text-[#176334]/75">Choose the type of assistance appropriate for your situation.</p>
                </div>
                <p id="current-step-label" class="text-sm text-[#176334]/75"></p>
            </div>

            <div class="mt-4 h-2 w-full rounded-full bg-[#176334]/10" role="progressbar" aria-valuemin="1" aria-valuemax="3" aria-valuenow="1">
                <div id="progress-fill" class="h-full w-0 rounded-full bg-[#6C9C02] transition-all"></div>
            </div>
        </header>

        <section id="assistance-selection" class="rounded-xl border border-[#176334]/20 bg-white p-6 shadow-sm">
            <h2 class="text-base/7 font-semibold text-[#176334]">Choose Assistance Type</h2>
            <p class="mt-1 text-sm/6 text-[#176334]/75">Below are the list of assistance types and their corresponding requirements.<br>Make sure the following requirements are met before applying for assistance.</p>

            <div class="mt-8 grid grid-cols-1 gap-6 md:grid-cols-3">
                <article class="rounded-lg border border-[#176334]/20 p-4 flex h-full flex-col">
                    <h3 class="font-semibold">Medical Assistance</h3>
                    <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-[#176334]/75 flex-1">
                        <li>Medical Certificate</li>
                        <li>Prescription</li>
                        <li>Applicant's Government ID</li>
                        <li>Beneficiary's Government ID</li>
                        <li>Applicant's Cedula</li>
                        <li>Barangay Indigency</li>
                        <li>Authorization Letter (optional)</li>
                    </ul>
                    <button type="button" class="choose-type mt-5 w-full rounded-md bg-[#176334] px-3 py-2 text-sm font-semibold text-white hover:opacity-90" data-type="Medical Assistance">
                        Apply for Medical
                    </button>
                </article>

                <article class="rounded-lg border border-[#176334]/20 p-4 flex h-full flex-col">
                    <h3 class="font-semibold">Hospital Assistance</h3>
                    <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-[#176334]/75 flex-1">
                        <li>Hospital Bill</li>
                        <li>Prescription</li>
                        <li>Medical Certificate/Abstract</li>
                        <li>Applicant's Government ID</li>
                        <li>Beneficiary's Government ID</li>
                        <li>Applicant's Cedula</li>
                        <li>Barangay Indigency</li>
                        <li>Authorization Letter (optional)</li>
                    </ul>
                    <button type="button" class="choose-type mt-5 w-full rounded-md bg-[#176334] px-3 py-2 text-sm font-semibold text-white hover:opacity-90" data-type="Hospital Assistance">
                        Apply for Hospital
                    </button>
                </article>

                <article class="rounded-lg border border-[#176334]/20 p-4 flex h-full flex-col">
                    <h3 class="font-semibold">Burial Assistance</h3>
                    <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-[#176334]/75 flex-1">
                        <li>Certified Copy of Birth Certificate</li>
                        <li>Applicant's Government ID</li>
                        <li>Applicant's Cedula</li>
                        <li>Beneficiary's Barangay Residency</li>
                        <li>Barangay Indigency</li>
                        <li>Authorization Letter (optional)</li>
                    </ul>
                    <button type="button" class="choose-type mt-5 w-full rounded-md bg-[#176334] px-3 py-2 text-sm font-semibold text-white hover:opacity-90" data-type="Burial Assistance">
                        Apply for Burial
                    </button>
                </article>
            </div>

            <div class="mt-6 flex justify-end">
                <a href="{{ url('/') }}" data-clear-apply-draft="true" class="inline-flex rounded-md border border-[#176334]/30 px-3 py-2 text-sm font-semibold text-[#176334] hover:bg-[#176334]/5">
                    Back to Directory
                </a>
            </div>
        </section>

        @php
            $initialCategory = old('category_name', '');
        @endphp
        <form id="application-form" method="POST" action="{{ route('applicant.apply.store') }}" enctype="multipart/form-data" class="{{ $initialCategory ? '' : 'hidden ' }}rounded-xl border border-[#176334]/20 bg-white p-6 shadow-sm">
            @csrf
            <input type="hidden" name="hp_token" value="">
            <input type="hidden" id="selected-assistance" name="category_name" value="{{ $initialCategory }}">

            <div class="rounded-lg border border-[#6C9C02]/40 bg-[#6C9C02]/10 px-4 py-3 text-sm text-[#176334]">
                Selected Assistance Type: <span id="selected-assistance-display" class="font-semibold">{{ $initialCategory ?: '-' }}</span>
            </div>

            <div class="mt-8 space-y-12">
                <section class="wizard-step border-b border-[#176334]/20 pb-10" data-step="1">
                    <h2 class="text-base/7 font-semibold text-[#176334]">Applicant Information</h2>
                    <p class="mt-1 text-sm/6 text-[#176334]/75">Provide your personal details as the applicant.</p>

                    <div class="mt-8 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-2">
                            <label class="block text-sm/6 font-medium text-gray-900">Last name</label>
                            <div class="mt-2"><input type="text" name="applicant[last_name]" value="{{ old('applicant.last_name') }}" required class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-[#176334] sm:text-sm/6" /></div>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm/6 font-medium text-gray-900">First name</label>
                            <div class="mt-2"><input type="text" name="applicant[first_name]" value="{{ old('applicant.first_name') }}" required class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-[#176334] sm:text-sm/6" /></div>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm/6 font-medium text-gray-900">Middle name</label>
                            <div class="mt-2"><input type="text" name="applicant[middle_name]" value="{{ old('applicant.middle_name') }}" required class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-[#176334] sm:text-sm/6" /></div>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm/6 font-medium text-gray-900">Sex</label>
                            <div class="mt-2 grid grid-cols-1">
                                <select required name="applicant[sex]" class="col-start-1 row-start-1 w-full appearance-none rounded-md bg-white py-1.5 pr-8 pl-3 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-[#176334] sm:text-sm/6">
                                    <option value="">Select</option>
                                    <option value="Male" @selected(old('applicant.sex') === 'Male')>Male</option>
                                    <option value="Female" @selected(old('applicant.sex') === 'Female')>Female</option>
                                </select>
                            </div>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm/6 font-medium text-gray-900">Date of birth</label>
                            <div class="mt-2"><input type="date" name="applicant[date_of_birth]" value="{{ old('applicant.date_of_birth') }}" required max="" id="applicant-dob" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-[#176334] sm:text-sm/6" /></div>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm/6 font-medium text-gray-900">Phone number</label>
                            <div class="mt-2"><input type="tel" id="applicant-phone" name="applicant[phone_number]" value="{{ old('applicant.phone_number') }}" required inputmode="numeric" pattern="^09\d{9}$" maxlength="11" data-numeric-phone="true" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-[#176334] sm:text-sm/6" placeholder="09xxxxxxxxx" /></div>
                        </div>
                        <div class="col-span-full">
                            <x-forms.ph-address-selector
                                prefix="applicant"
                                name="applicant[address]"
                                label="Address"
                                :value="old('applicant.address')"
                            />
                        </div>
                    </div>
                </section>

                <section class="wizard-step hidden border-b border-[#176334]/20 pb-10" data-step="2">
                    <h2 class="text-base/7 font-semibold text-[#176334]">Beneficiary Information</h2>
                    <p class="mt-1 text-sm/6 text-[#176334]/75">Provide beneficiary details and your relationship.</p>

                    <div class="mt-8 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-2">
                            <label class="block text-sm/6 font-medium text-gray-900 dark:text-white">Last name</label>
                            <div class="mt-2"><input type="text" name="beneficiary[last_name]" value="{{ old('beneficiary.last_name') }}" required class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:focus:outline-indigo-500" /></div>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm/6 font-medium text-gray-900 dark:text-white">First name</label>
                            <div class="mt-2"><input type="text" name="beneficiary[first_name]" value="{{ old('beneficiary.first_name') }}" required class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:focus:outline-indigo-500" /></div>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm/6 font-medium text-gray-900 dark:text-white">Middle name</label>
                            <div class="mt-2"><input type="text" name="beneficiary[middle_name]" value="{{ old('beneficiary.middle_name') }}" required class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:focus:outline-indigo-500" /></div>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm/6 font-medium text-gray-900 dark:text-white">Sex</label>
                            <div class="mt-2 grid grid-cols-1">
                                <select required name="beneficiary[sex]" class="col-start-1 row-start-1 w-full appearance-none rounded-md bg-white py-1.5 pr-8 pl-3 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:focus:outline-indigo-500">
                                    <option value="">Select</option>
                                    <option value="Male" @selected(old('beneficiary.sex') === 'Male')>Male</option>
                                    <option value="Female" @selected(old('beneficiary.sex') === 'Female')>Female</option>
                                </select>
                            </div>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm/6 font-medium text-gray-900 dark:text-white">Date of birth</label>
                            <div class="mt-2"><input type="date" name="beneficiary[date_of_birth]" value="{{ old('beneficiary.date_of_birth') }}" required class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:focus:outline-indigo-500" /></div>
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-sm/6 font-medium text-gray-900 dark:text-white">Relationship to beneficiary</label>
                            <div class="mt-2 grid grid-cols-1">
                                <select id="relationship-to-beneficiary" name="beneficiary[relationship]" required class="col-start-1 row-start-1 w-full appearance-none rounded-md bg-white py-1.5 pr-8 pl-3 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-[#176334] sm:text-sm/6">
                                    <option value="">Select relationship</option>
                                    <option value="Self" @selected(old('beneficiary.relationship') === 'Self')>Self</option>
                                    <option value="Parent" @selected(old('beneficiary.relationship') === 'Parent')>Parent</option>
                                    <option value="Sibling" @selected(old('beneficiary.relationship') === 'Sibling')>Sibling</option>
                                    <option value="Spouse" @selected(old('beneficiary.relationship') === 'Spouse')>Spouse</option>
                                    <option value="Child" @selected(old('beneficiary.relationship') === 'Child')>Child</option>
                                    <option value="Representative" @selected(old('beneficiary.relationship') === 'Representative')>Representative</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-span-full">
                            <x-forms.ph-address-selector
                                prefix="beneficiary"
                                name="beneficiary[address]"
                                label="Address"
                                :value="old('beneficiary.address')"
                            />
                        </div>
                    </div>
                </section>

                <section class="wizard-step hidden" data-step="3">
                    <h2 class="text-base/7 font-semibold text-[#176334]">Document Uploads</h2>
                    <p class="mt-1 text-sm/6 text-[#176334]/75">Upload each requirement separately so each file can be stored individually.</p>

                    <div class="mt-8">
                        <div class="requirement-upload-group hidden space-y-6" data-type-upload="Medical Assistance">
                            <div>
                                <label class="block text-sm/6 font-medium text-gray-900">Medical Certificate</label>
                                <div class="mt-2 rounded-lg border border-dashed border-[#176334]/30 px-6 py-6">
                                    <input type="file" required name="requirements[medical_certificate]" class="block w-full text-sm text-gray-900 file:mr-3 file:rounded-md file:border-0 file:bg-gray-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:opacity-90" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm/6 font-medium text-gray-900">Prescription</label>
                                <div class="mt-2 rounded-lg border border-dashed border-[#176334]/30 px-6 py-6">
                                    <input type="file" required name="requirements[prescription]" class="block w-full text-sm text-gray-900 file:mr-3 file:rounded-md file:border-0 file:bg-gray-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:opacity-90" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm/6 font-medium text-gray-900">Applicant's Government ID</label>
                                <div class="mt-2 rounded-lg border border-dashed border-[#176334]/30 px-6 py-6">
                                    <input type="file" required name="requirements[applicant_government_id]" class="block w-full text-sm text-gray-900 file:mr-3 file:rounded-md file:border-0 file:bg-gray-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:opacity-90" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm/6 font-medium text-gray-900">Beneficiary's Government ID</label>
                                <div class="mt-2 rounded-lg border border-dashed border-[#176334]/30 px-6 py-6">
                                    <input type="file" required name="requirements[beneficiary_government_id]" class="block w-full text-sm text-gray-900 file:mr-3 file:rounded-md file:border-0 file:bg-gray-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:opacity-90" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm/6 font-medium text-gray-900">Applicant's Cedula</label>
                                <div class="mt-2 rounded-lg border border-dashed border-[#176334]/30 px-6 py-6">
                                    <input type="file" required name="requirements[applicant_cedula]" class="block w-full text-sm text-gray-900 file:mr-3 file:rounded-md file:border-0 file:bg-gray-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:opacity-90" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm/6 font-medium text-gray-900">Barangay Indigency</label>
                                <div class="mt-2 rounded-lg border border-dashed border-[#176334]/30 px-6 py-6">
                                    <input type="file" required name="requirements[barangay_indigency]" class="block w-full text-sm text-gray-900 file:mr-3 file:rounded-md file:border-0 file:bg-gray-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:opacity-90" />
                                </div>
                            </div>
                            <div class="authorization-letter-field hidden">
                                <label class="block text-sm/6 font-medium text-gray-900">Authorization Letter (required for representative)</label>
                                <div class="mt-2 rounded-lg border border-dashed border-[#176334]/30 px-6 py-6">
                                    <input type="file" required name="requirements[authorization_letter]" class="block w-full text-sm text-gray-900 file:mr-3 file:rounded-md file:border-0 file:bg-gray-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:opacity-90" disabled />
                                </div>
                            </div>
                        </div>

                        <div class="requirement-upload-group hidden space-y-6" data-type-upload="Hospital Assistance">
                            <div>
                                <label class="block text-sm/6 font-medium text-gray-900">Hospital Bill</label>
                                <div class="mt-2 rounded-lg border border-dashed border-[#176334]/30 px-6 py-6">
                                    <input type="file" required name="requirements[hospital_bill]" class="block w-full text-sm text-gray-900 file:mr-3 file:rounded-md file:border-0 file:bg-gray-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:opacity-90" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm/6 font-medium text-gray-900">Prescription</label>
                                <div class="mt-2 rounded-lg border border-dashed border-[#176334]/30 px-6 py-6">
                                    <input type="file" required name="requirements[prescription]" class="block w-full text-sm text-gray-900 file:mr-3 file:rounded-md file:border-0 file:bg-gray-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:opacity-90" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm/6 font-medium text-gray-900">Medical Certificate/Abstract</label>
                                <div class="mt-2 rounded-lg border border-dashed border-[#176334]/30 px-6 py-6">
                                    <input type="file" required name="requirements[medical_certificate_abstract]" class="block w-full text-sm text-gray-900 file:mr-3 file:rounded-md file:border-0 file:bg-gray-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:opacity-90" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm/6 font-medium text-gray-900">Applicant's Government ID</label>
                                <div class="mt-2 rounded-lg border border-dashed border-[#176334]/30 px-6 py-6">
                                    <input type="file" required name="requirements[applicant_government_id]" class="block w-full text-sm text-gray-900 file:mr-3 file:rounded-md file:border-0 file:bg-gray-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:opacity-90" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm/6 font-medium text-gray-900">Beneficiary's Government ID</label>
                                <div class="mt-2 rounded-lg border border-dashed border-[#176334]/30 px-6 py-6">
                                    <input type="file" required name="requirements[beneficiary_government_id]" class="block w-full text-sm text-gray-900 file:mr-3 file:rounded-md file:border-0 file:bg-gray-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:opacity-90" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm/6 font-medium text-gray-900">Applicant's Cedula</label>
                                <div class="mt-2 rounded-lg border border-dashed border-[#176334]/30 px-6 py-6">
                                    <input type="file" required name="requirements[applicant_cedula]" class="block w-full text-sm text-gray-900 file:mr-3 file:rounded-md file:border-0 file:bg-gray-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:opacity-90" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm/6 font-medium text-gray-900">Barangay Indigency</label>
                                <div class="mt-2 rounded-lg border border-dashed border-[#176334]/30 px-6 py-6">
                                    <input type="file" required name="requirements[barangay_indigency]" class="block w-full text-sm text-gray-900 file:mr-3 file:rounded-md file:border-0 file:bg-gray-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:opacity-90" />
                                </div>
                            </div>
                            <div class="authorization-letter-field hidden">
                                <label class="block text-sm/6 font-medium text-gray-900">Authorization Letter (required for representative)</label>
                                <div class="mt-2 rounded-lg border border-dashed border-[#176334]/30 px-6 py-6">
                                    <input type="file" required name="requirements[authorization_letter]" class="block w-full text-sm text-gray-900 file:mr-3 file:rounded-md file:border-0 file:bg-gray-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:opacity-90" disabled />
                                </div>
                            </div>
                        </div>

                        <div class="requirement-upload-group hidden space-y-6" data-type-upload="Burial Assistance">
                            <div>
                                <label class="block text-sm/6 font-medium text-gray-900">Certified Copy of Birth Certificate</label>
                                <div class="mt-2 rounded-lg border border-dashed border-[#176334]/30 px-6 py-6">
                                    <input type="file" required name="requirements[certified_birth_certificate]" class="block w-full text-sm text-gray-900 file:mr-3 file:rounded-md file:border-0 file:bg-gray-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:opacity-90" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm/6 font-medium text-gray-900">Applicant's Government ID</label>
                                <div class="mt-2 rounded-lg border border-dashed border-[#176334]/30 px-6 py-6">
                                    <input type="file" required name="requirements[applicant_government_id]" class="block w-full text-sm text-gray-900 file:mr-3 file:rounded-md file:border-0 file:bg-gray-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:opacity-90" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm/6 font-medium text-gray-900">Applicant's Cedula</label>
                                <div class="mt-2 rounded-lg border border-dashed border-[#176334]/30 px-6 py-6">
                                    <input type="file" required name="requirements[applicant_cedula]" class="block w-full text-sm text-gray-900 file:mr-3 file:rounded-md file:border-0 file:bg-gray-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:opacity-90" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm/6 font-medium text-gray-900">Beneficiary's Barangay Residency</label>
                                <div class="mt-2 rounded-lg border border-dashed border-[#176334]/30 px-6 py-6">
                                    <input type="file" required name="requirements[beneficiary_barangay_residency]" class="block w-full text-sm text-gray-900 file:mr-3 file:rounded-md file:border-0 file:bg-gray-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:opacity-90" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm/6 font-medium text-gray-900">Barangay Indigency</label>
                                <div class="mt-2 rounded-lg border border-dashed border-[#176334]/30 px-6 py-6">
                                    <input type="file" required name="requirements[barangay_indigency]" class="block w-full text-sm text-gray-900 file:mr-3 file:rounded-md file:border-0 file:bg-gray-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:opacity-90" />
                                </div>
                            </div>
                            <div class="authorization-letter-field hidden">
                                <label class="block text-sm/6 font-medium text-gray-900">Authorization Letter (required for representative)</label>
                                <div class="mt-2 rounded-lg border border-dashed border-[#176334]/30 px-6 py-6">
                                    <input type="file" required name="requirements[authorization_letter]" class="block w-full text-sm text-gray-900 file:mr-3 file:rounded-md file:border-0 file:bg-gray-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:opacity-90" disabled />
                                </div>
                            </div>
                        </div>
                    </div>

                    <p class="mt-4 text-xs/5 text-[#176334]/75">Allowed formats: JPG or JPEG images only. Max size: 1MB per file. Uploaded images are converted to PDF automatically.</p>
                </section>
            </div>

            <div class="mt-8 flex items-center justify-between gap-3">
                <a href="{{ url('/') }}" data-clear-apply-draft="true" class="text-sm/6 font-semibold text-[#176334]">Cancel</a>

                <div class="flex items-center gap-2">
                    <button type="button" id="btn-prev" class="hidden rounded-md border border-[#176334]/30 bg-white px-3 py-2 text-sm font-semibold text-[#176334] hover:bg-[#176334]/5">Previous</button>
                    <button type="button" id="btn-next" class="rounded-md bg-[#176334] px-3 py-2 text-sm font-semibold text-white hover:opacity-90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#6C9C02]">Next</button>
                    <button type="button" id="btn-final" class="hidden items-center gap-2 rounded-md bg-[#6C9C02] px-3 py-2 text-sm font-semibold text-white hover:opacity-90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#6C9C02] disabled:cursor-not-allowed disabled:opacity-70">
                        <svg id="btn-final-spinner" class="hidden h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"></path>
                        </svg>
                        <span id="btn-final-label">Submit Application</span>
                    </button>
                </div>
            </div>
        </form>
    </main>

    <script>
        (() => {
            const totalSteps = 3;
            let currentStep = 1;
            let hasStartedForm = false;

            const steps = Array.from(document.querySelectorAll('.wizard-step'));
            const assistanceSelection = document.getElementById('assistance-selection');
            const form = document.getElementById('application-form');
            const progressFill = document.getElementById('progress-fill');
            const stepLabel = document.getElementById('current-step-label');
            const btnPrev = document.getElementById('btn-prev');
            const btnNext = document.getElementById('btn-next');
            const btnFinal = document.getElementById('btn-final');
            const selectedAssistanceInput = document.getElementById('selected-assistance');
            const selectedAssistanceDisplay = document.getElementById('selected-assistance-display');
            const btnFinalSpinner = document.getElementById('btn-final-spinner');
            const btnFinalLabel = document.getElementById('btn-final-label');
            const cancelDraftLinks = Array.from(document.querySelectorAll('[data-clear-apply-draft="true"]'));
            const initialCategory = @json($initialCategory);
            const relationshipSelect = document.getElementById('relationship-to-beneficiary');
            const requirementUploadGroups = Array.from(document.querySelectorAll('.requirement-upload-group'));
            const authorizationLetterFields = Array.from(document.querySelectorAll('.authorization-letter-field'));
            const phoneInputs = Array.from(document.querySelectorAll('[data-numeric-phone="true"]'));
            const draftStorageKey = 'aics_apply_form_draft_v1';
            const fileDraftStorageKey = 'aics_apply_file_draft_v1';
            const fileDraftMaxBytes = 4 * 1024 * 1024;
            let isSubmitting = false;

            const loadJsonFromStorage = (key) => {
                try {
                    const raw = localStorage.getItem(key);
                    return raw ? JSON.parse(raw) : null;
                } catch (error) {
                    return null;
                }
            };

            const saveJsonToStorage = (key, value) => {
                try {
                    localStorage.setItem(key, JSON.stringify(value));
                    return true;
                } catch (error) {
                    return false;
                }
            };

            const clearApplyDraftState = () => {
                try {
                    localStorage.removeItem(draftStorageKey);
                    localStorage.removeItem(fileDraftStorageKey);
                } catch (error) {
                    // Ignore storage cleanup errors and continue navigation.
                }

                selectedAssistanceInput.value = '';
                selectedAssistanceDisplay.textContent = '-';
            };

            const readFileAsDataUrl = (file) => new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = () => resolve(String(reader.result || ''));
                reader.onerror = () => reject(reader.error || new Error('Unable to read file.'));
                reader.readAsDataURL(file);
            });

            function persistFormDraft() {
                const fields = Array.from(form.querySelectorAll('input[name], select[name], textarea[name]'));
                const values = {};

                fields.forEach((field) => {
                    if (!field.name || field.disabled || field.type === 'file' || field.name === '_token') {
                        return;
                    }

                    if ((field.type === 'checkbox' || field.type === 'radio')) {
                        values[field.name] = field.checked;
                        return;
                    }

                    values[field.name] = field.value;
                });

                saveJsonToStorage(draftStorageKey, {
                    updatedAt: Date.now(),
                    values,
                });
            }

            function restoreFormDraft() {
                const draft = loadJsonFromStorage(draftStorageKey);

                if (!draft || typeof draft !== 'object' || !draft.values || typeof draft.values !== 'object') {
                    return;
                }

                const fields = Array.from(form.querySelectorAll('input[name], select[name], textarea[name]'));

                fields.forEach((field) => {
                    if (!field.name || field.type === 'file' || field.name === '_token') {
                        return;
                    }

                    if (!Object.prototype.hasOwnProperty.call(draft.values, field.name)) {
                        return;
                    }

                    const value = draft.values[field.name];

                    if (field.type === 'checkbox' || field.type === 'radio') {
                        if (!field.checked && Boolean(value)) {
                            field.checked = true;
                        }
                        return;
                    }

                    if (!field.value && typeof value === 'string') {
                        field.value = value;
                    }
                });

                const categoryFromDraft = typeof draft.values['category_name'] === 'string'
                    ? draft.values['category_name']
                    : '';

                if (!selectedAssistanceInput.value && categoryFromDraft) {
                    selectedAssistanceInput.value = categoryFromDraft;
                    selectedAssistanceDisplay.textContent = categoryFromDraft;
                }
            }

            function persistAddressPreviewFromHiddenInputs() {
                document.querySelectorAll('[data-ph-address-selector]').forEach((wrapper) => {
                    const hiddenAddressInput = wrapper.querySelector('[data-role="composed-address"]');
                    const addressPreviewInput = wrapper.querySelector('[data-role="address-preview"]');

                    if (!hiddenAddressInput || !addressPreviewInput) {
                        return;
                    }

                    if (hiddenAddressInput.value && !addressPreviewInput.value) {
                        addressPreviewInput.value = hiddenAddressInput.value;
                    }
                });
            }

            async function persistFileDraft(input) {
                if (!(input instanceof HTMLInputElement) || input.type !== 'file' || !input.name) {
                    return;
                }

                const draft = loadJsonFromStorage(fileDraftStorageKey) || { updatedAt: Date.now(), files: {} };
                draft.files = draft.files || {};

                if (!input.files || input.files.length === 0) {
                    delete draft.files[input.name];
                    draft.updatedAt = Date.now();
                    saveJsonToStorage(fileDraftStorageKey, draft);
                    return;
                }

                const file = input.files[0];
                const dataUrl = await readFileAsDataUrl(file);

                draft.files[input.name] = {
                    name: file.name,
                    type: file.type,
                    lastModified: file.lastModified,
                    size: file.size,
                    dataUrl,
                };

                const totalSize = Object.values(draft.files)
                    .reduce((sum, current) => sum + Number(current?.size ?? 0), 0);

                if (totalSize > fileDraftMaxBytes) {
                    delete draft.files[input.name];
                }

                draft.updatedAt = Date.now();
                saveJsonToStorage(fileDraftStorageKey, draft);
            }

            async function restoreFileDrafts() {
                const draft = loadJsonFromStorage(fileDraftStorageKey);

                if (!draft || typeof draft !== 'object' || !draft.files || typeof draft.files !== 'object') {
                    return;
                }

                const fileInputs = Array.from(form.querySelectorAll('input[type="file"][name]'));

                for (const input of fileInputs) {
                    if (!(input instanceof HTMLInputElement) || !input.name || input.files?.length) {
                        continue;
                    }

                    const fileDraft = draft.files[input.name];

                    if (!fileDraft || typeof fileDraft.dataUrl !== 'string' || fileDraft.dataUrl === '') {
                        continue;
                    }

                    try {
                        const response = await fetch(fileDraft.dataUrl);
                        const blob = await response.blob();
                        const file = new File([blob], fileDraft.name || 'draft-upload.jpg', {
                            type: fileDraft.type || 'image/jpeg',
                            lastModified: Number(fileDraft.lastModified || Date.now()),
                        });

                        const transfer = new DataTransfer();
                        transfer.items.add(file);
                        input.files = transfer.files;
                    } catch (error) {
                        // If restoration fails (browser/security constraints), user can pick file again.
                    }
                }
            }

            function setSubmittingState(value) {
                isSubmitting = value;

                btnFinal.disabled = value;
                btnNext.disabled = value;
                btnPrev.disabled = value;

                btnFinalSpinner?.classList.toggle('hidden', !value);

                if (btnFinalLabel) {
                    btnFinalLabel.textContent = value ? 'Submitting...' : 'Submit Application';
                }
            }

            function enforcePhoneDigits(input) {
                input.value = input.value.replace(/\D+/g, '').slice(0, 11);
            }

            phoneInputs.forEach((input) => {
                input.addEventListener('input', () => enforcePhoneDigits(input));
                enforcePhoneDigits(input);
            });

            function updateAuthorizationLetterRequirement() {
                const isRepresentative = relationshipSelect?.value === 'Representative';

                authorizationLetterFields.forEach((field) => {
                    field.classList.toggle('hidden', !isRepresentative);

                    const fileInput = field.querySelector('input[type="file"]');
                    if (fileInput) {
                        fileInput.disabled = !isRepresentative;
                    }
                });
            }

            function updateRequirementUploadGroup(type) {
                requirementUploadGroups.forEach((group) => {
                    const isTargetGroup = group.dataset.typeUpload === type;

                    group.classList.toggle('hidden', !isTargetGroup);
                    group.querySelectorAll('input, select, textarea').forEach((control) => {
                        control.disabled = !isTargetGroup;
                    });
                });

                updateAuthorizationLetterRequirement();
                persistFormDraft();
            }

            function validateCurrentStep() {
                const currentStepElement = steps.find((step) => Number(step.dataset.step) === currentStep);
                if (!currentStepElement) {
                    return true;
                }

                const controls = Array.from(currentStepElement.querySelectorAll('input, select, textarea'));

                for (const control of controls) {
                    if (control.disabled) {
                        continue;
                    }

                    if (!control.checkValidity()) {
                        control.reportValidity();
                        return false;
                    }
                }

                return true;
            }

            function refreshHeader() {
                if (!hasStartedForm) {
                    progressFill.style.width = '0%';
                    stepLabel.textContent = 'Select assistance type to start';
                    return;
                }

                const progressPercent = (currentStep / totalSteps) * 100;
                progressFill.style.width = `${progressPercent}%`;
                stepLabel.textContent = `Page ${currentStep} of ${totalSteps}`;
            }

            function goToStep(stepNumber) {
                currentStep = stepNumber;

                steps.forEach((step) => {
                    const isTarget = Number(step.dataset.step) === currentStep;
                    step.classList.toggle('hidden', !isTarget);
                });

                btnPrev.classList.toggle('hidden', currentStep === 1);
                btnNext.classList.toggle('hidden', currentStep === totalSteps);
                btnFinal.classList.toggle('hidden', currentStep !== totalSteps);
                btnFinal.classList.toggle('inline-flex', currentStep === totalSteps);

                refreshHeader();
            }

            document.querySelectorAll('.choose-type').forEach((button) => {
                button.addEventListener('click', () => {
                    const type = button.dataset.type || '';

                    selectedAssistanceInput.value = type;
                    selectedAssistanceDisplay.textContent = type;
                    updateRequirementUploadGroup(type);

                    hasStartedForm = true;
                    assistanceSelection.classList.add('hidden');
                    form.classList.remove('hidden');
                    updateAuthorizationLetterRequirement();
                    persistFormDraft();

                    goToStep(1);
                });
            });

            relationshipSelect?.addEventListener('change', () => {
                updateAuthorizationLetterRequirement();
                persistFormDraft();
            });

            cancelDraftLinks.forEach((link) => {
                link.addEventListener('click', () => {
                    clearApplyDraftState();
                });
            });

            form.addEventListener('input', (event) => {
                const target = event.target;

                if (!(target instanceof HTMLElement)) {
                    return;
                }

                if (target instanceof HTMLInputElement && target.type === 'file') {
                    persistFileDraft(target);
                    return;
                }

                persistFormDraft();
            });

            form.addEventListener('change', (event) => {
                const target = event.target;

                if (target instanceof HTMLInputElement && target.type === 'file') {
                    persistFileDraft(target);
                    return;
                }

                persistFormDraft();
            });

            btnPrev.addEventListener('click', () => {
                if (currentStep > 1) {
                    goToStep(currentStep - 1);
                }
            });

            btnNext.addEventListener('click', () => {
                if (currentStep < totalSteps && validateCurrentStep()) {
                    goToStep(currentStep + 1);
                }
            });

            btnFinal.addEventListener('click', () => {
                if (isSubmitting) {
                    return;
                }

                if (!validateCurrentStep()) {
                    return;
                }

                setSubmittingState(true);
                form.requestSubmit();
            });

            form.addEventListener('submit', (event) => {
                if (currentStep !== totalSteps) {
                    event.preventDefault();

                    if (currentStep < totalSteps) {
                        goToStep(currentStep + 1);
                    }

                    return;
                }

                if (!validateCurrentStep()) {
                    event.preventDefault();
                    return;
                }

                if (!isSubmitting) {
                    setSubmittingState(true);
                }
            });

            if (initialCategory) {
                hasStartedForm = true;
                assistanceSelection.classList.add('hidden');
                form.classList.remove('hidden');
                selectedAssistanceInput.value = initialCategory;
                selectedAssistanceDisplay.textContent = initialCategory;
                updateRequirementUploadGroup(initialCategory);
                goToStep(1);
            }

            restoreFormDraft();

            if (!initialCategory && selectedAssistanceInput.value) {
                hasStartedForm = true;
                assistanceSelection.classList.add('hidden');
                form.classList.remove('hidden');
                selectedAssistanceDisplay.textContent = selectedAssistanceInput.value;
                updateRequirementUploadGroup(selectedAssistanceInput.value);
                goToStep(1);
            }

            persistAddressPreviewFromHiddenInputs();
            restoreFileDrafts();

            refreshHeader();
            updateAuthorizationLetterRequirement();
            persistFormDraft();

            const dobInput = document.getElementById('applicant-dob');
            if (dobInput) {
                const today = new Date();
                const minDate = new Date(today.getFullYear() - 18, today.getMonth(), today.getDate());
                dobInput.max = minDate.toISOString().split('T')[0];
            }
        })();
    </script>
</body>
</html>
