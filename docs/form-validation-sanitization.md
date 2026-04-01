# Form Validation and Sanitization

This document tracks the current lightweight frontend validation/sanitization behavior for the Add User modal.

Current approach:

- Frontend performs basic sanitization and validation for better UX.
- Backend remains the source of truth using Laravel validation rules in `AuthIntegrationController::storeUser`.

## Frontend module

- `resources/js/forms/validation-sanitization.js`

## What it currently handles

- Name sanitization (letters/spaces/apostrophe/hyphen + trim)
- Email sanitization (trim/lowercase/remove spaces)
- Password trim sanitization
- Basic Add User checks:
    - first name min length
    - last name min length
    - email format
    - password min length (6)
    - required role
- Show/hide password toggle for Add User modal

## Important note

Laravel backend validation (including `Password` rule) is still mandatory and authoritative.
