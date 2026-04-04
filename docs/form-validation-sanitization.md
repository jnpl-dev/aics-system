# Form Validation and Sanitization

This document tracks current validation and sanitization behavior across admin and public forms.

Current approach:

- Backend remains the source of truth via Filament + Laravel validation rules.
- Public applicant intake uses a dedicated Laravel `FormRequest` for server-side sanitization/validation.

## Runtime status

- Legacy Add User frontend script `resources/js/forms/validation-sanitization.js` has been removed.
- Legacy dashboard Add User modal flow was retired with the user-management Filament migration.
- Public applicant form (`/apply`) now submits to server-side validation endpoint (`POST /apply`).
- Reusable public form components are in use (`x-forms.page-feedback`, `x-forms.ph-address-selector`).

## What it currently handles

### Admin user management (Filament)

- Server-side sanitization in `CreateUser::mutateFormDataBeforeCreate()`:
    - name cleanup (letters/spaces/apostrophe/hyphen + trim)
    - lowercase/trim email normalization
- Filament form rules for user creation/edit actions (required fields, password rules, confirmation checks).
- Supabase provisioning/reconciliation checks in `CreateUser` with fallback behavior based on configuration.

### Public applicant apply flow (`/apply`)

- Request class: `App\Http\Requests\StoreApplicantApplicationRequest`
- Controller endpoint: `App\Http\Controllers\ApplicantApplicationController@store`
- Server-side sanitization in `prepareForValidation()`:
    - trim + strip tags + collapse whitespace for text fields
    - normalize applicant phone number to digits-only
- Validation coverage:
    - required applicant and beneficiary fields
    - allowed value checks (category, sex, relationship)
    - phone format enforcement (`09XXXXXXXXX`)
    - conditional per-category requirement uploads
    - conditional authorization letter when relationship is `Representative`
    - file constraints (pdf/jpg/jpeg/png, max 5MB)

## Important note

Validation and sanitization are enforced server-side and remain authoritative.

Frontend checks (required inputs, step gating, numeric input guards, cascading selectors) are UX aids only and must always be backed by server-side rules.
