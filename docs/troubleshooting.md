# Troubleshooting Guide

This file records real issues encountered during development, with root cause analysis and repeatable fixes.

## 1) Add User modal closes but no user is saved

### Symptoms

- Clicking **Save User** closes the Add User modal
- No validation error appears
- No new row is shown in User Management table
- No user is added in `user` table

### Root Cause

Invalid nested-form structure in `User Management` tab caused submit interception:

- The Add User form existed inside/alongside another active form context
- Browser submission behavior became inconsistent and did not always post to `/admin/users`

### Fix Applied

- Refactored `resources/views/admin/tabs/user-management.blade.php`
- Isolated Add User modal/form from search/filter form
- Kept each form with a single responsibility

### Prevention Checklist

- Never nest forms in Blade components
- Keep modal action forms outside filter/search forms
- When submit appears to do nothing, inspect actual request target in Network tab

---

## 2) Add User request succeeds in UI but DB insert fails for some roles

### Symptoms

- Add User appears to submit
- Specific roles fail to create users
- SQL/insert behavior inconsistent by role value

### Root Cause

Runtime MySQL enum in `user.role` was out-of-sync with docs and validation:

- DB had legacy value `mayors_office`
- DB did not include newer roles (`mayor_office_staff`, `system_admin` at the time)

### Fix Applied

- Added and ran migration: `2026_04_02_000004_align_user_role_enum_with_rbac.php`
- Updated legacy role value mapping
- Aligned runtime enum values with documented RBAC values

### Prevention Checklist

- After role changes in docs/validation, immediately align DB enum via migration
- Validate runtime schema (`SHOW CREATE TABLE user`) when role-related inserts fail
- Avoid manual DB edits for enum values outside migrations

---

## 3) Newly created user cannot log in ("Invalid login credentials")

### Symptoms

- Admin creates user successfully in local DB
- New user gets `Invalid login credentials` on `/login`

### Root Cause

Auth source mismatch:

- Login authenticates against **Supabase Auth** email/password
- `/admin/users` previously created only local MySQL `user` row
- No Supabase credential existed for that email

### Fix Applied

- Updated `AuthIntegrationController::storeUser` to provision Supabase Auth user via:
    - `POST /auth/v1/admin/users` (service role)
- Local user creation now happens with Supabase provisioning in the same flow
- Added graceful error handling for Supabase connection/API failures
- Added rollback cleanup attempt when local DB save fails after Supabase create

### Prevention Checklist

- Any admin account creation must provision both:
    - Supabase Auth credentials
    - local `user` row
- Ensure `.env` has:
    - `SUPABASE_URL`
    - `SUPABASE_SERVICE_ROLE_KEY`
- If login fails for a newly created user, verify both data stores (Supabase + local DB)

---

## 4) Tests fail after introducing external auth provisioning

### Symptoms

- Feature tests return HTTP 500 during `/admin/users`
- Error shows unresolved host/network call to Supabase test URL

### Root Cause

HTTP fake did not include the new Supabase Admin endpoint:

- Missing fake for `https://example.supabase.co/auth/v1/admin/users`

### Fix Applied

- Added `Http::fake` mapping for the Supabase admin users endpoint in user-creation tests
- Added runtime try/catch around provisioning call to return controlled 422 errors instead of 500

### Prevention Checklist

- After adding any external HTTP call, update tests with all expected endpoint fakes
- Prefer explicit endpoint fakes over broad wildcards for deterministic behavior

---

## Quick Diagnostics Commands

Use these when similar issues reappear:

1. Verify runtime user table schema includes expected roles
2. Verify local user exists by email
3. Verify Supabase user exists for same email
4. Check browser Network tab for `/admin/users` request status and response payload

(Use project-standard tools/tests before manual DB edits.)
