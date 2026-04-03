# Form Validation and Sanitization

This document tracks the current validation and sanitization behavior for user-management forms.

Current approach:

- Filament form schemas and page handlers are used for user-management input handling.
- Backend remains the source of truth via Filament + Laravel validation rules.

## Runtime status

- Legacy Add User frontend script `resources/js/forms/validation-sanitization.js` has been removed.
- Legacy dashboard Add User modal flow was retired with the user-management Filament migration.

## What it currently handles

- Server-side sanitization in `CreateUser::mutateFormDataBeforeCreate()`:
    - name cleanup (letters/spaces/apostrophe/hyphen + trim)
    - lowercase/trim email normalization
- Filament form rules for user creation/edit actions (required fields, password rules, confirmation checks).
- Supabase provisioning/reconciliation checks in `CreateUser` with fallback behavior based on configuration.

## Important note

Validation and sanitization are enforced server-side and remain authoritative.
