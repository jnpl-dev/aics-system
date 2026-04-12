# AICS Digital Application and Notification System

## Overview

A web-based system for the Assistance to Individuals in Crisis Situation (AICS) program of a local government unit in the Philippines. The system digitizes the application, review, approval, and notification process for financial assistance.

## Purpose

- Allow applicants to submit assistance applications digitally or through staff-assisted walk-in submission
- Track applications through a multi-stage approval workflow
- Automate SMS notifications at every key stage
- Generate reports for management oversight

## Engineering Rule: Performance First

Performance optimization is a continuous requirement for this project, not a one-time task.

All feature work should include:

- efficient query/sort/filter paths,
- practical caching/state persistence where appropriate,
- responsive UI behavior under real workloads,
- and documentation updates when runtime performance behavior changes.

## Actors

| Actor                | Role                                                |
| -------------------- | --------------------------------------------------- |
| Applicant            | Submits and tracks their own application            |
| AICS Staff           | Reviews, validates, codes, and manages applications |
| MSWD Officer         | Conducts social case study, prepares voucher        |
| Mayor's Office Staff | Reviews and approves assistance code                |
| Accountant           | Verifies voucher calculations                       |
| Treasurer            | Checks fund availability and prepares cheque        |
| System Administrator | Manages user accounts, roles, and system settings   |

## Tech Stack

1. Backend / Core
   Framework: Laravel
   Database: MySQL
   👉 Laravel will now handle:
   File management
   Automated SMS triggers (important update)

2. Authentication
   Supabase Auth

2️. SMS System (Expanded Role)
Provider: PhilSMS
🔐 Uses of SMS in the system:

1. Automated Notifications
   Examples:
   📩 “Your document has been approved”
   📩 “Your application is under review”
   📩 “Your request has been completed”
   📩 “Please upload missing documents”

3️. Frontend / Scanning
Templating: Blade
Dynamic sections: Server-rendered Blade fragments (`/dashboard/content/{tab}`)
Client-side interactivity: Custom JavaScript (Vite)
Styling: Tailwind CSS
Asset bundler: Vite (built into Laravel)
SPA-like behavior: single dashboard shell + async tab content loading
Camera input + auto-crop:
opencv.js (auto detect edges)
ocropper.js fallback

Design defaults for UI consistency:

- Palette:
    - `--dark-emerald: #176334ff`
    - `--lime-moss: #6C9C02ff`
    - `--snow: #FFFDFFff`
- Fonts:
    - `Inter`
    - `Public Sans`

4️. Storage
Cloud Storage: Supabase Storage
Laravel uploads files → Supabase
MySQL stores file metadata

5️. Hosting
Render (Laravel app)
MySQL (Render or external)
Supabase (storage only)

## Quick Links

- [Progress Tracker](./progress.md)
- [Database Schema](./schema.md)
- [Workflow](./workflow.md)
- [RBAC](./rbac.md)
- [Entities](./entities.md)
- [Decisions](./decisions.md)
- [Notifications](./notifications.md)
- [API](./api.md)
- [Frontend Development Guide](./frontend.md)
- [Form Validation and Sanitization](./form-validation-sanitization.md)
- [Troubleshooting](./troubleshooting.md)
- [Collaborator Installation Guide](./installation-collaborators.md)

## Supabase Auth Setup (Phase 2)

Required environment values:

- `SUPABASE_URL`
- `SUPABASE_ANON_KEY`
- `SUPABASE_SERVICE_ROLE_KEY` (server-side only)
- `SUPABASE_JWT_ISSUER`
- `SUPABASE_JWKS_URL`
- `SUPABASE_AUTH_USER_ENDPOINT` (default: `/auth/v1/user`)

Implemented middleware aliases:

- `supabase.auth` → verifies bearer token with Supabase and resolves local account by email
- `role:<role1>,<role2>` → enforces active account status and role-based route access

Example route protection:

- `Route::middleware(['supabase.auth', 'role:admin'])->group(...)`
- `Route::middleware(['supabase.auth', 'role:aics_staff,mswd_officer'])->group(...)`

Current implemented auth flows:

### Filament staff login flow (canonical)

1. User opens `/login` (Filament login page).
2. User submits email/password credentials.
3. System creates OTP challenge and redirects immediately to `/otp`.
4. OTP page sends/handles 6-digit email OTP verification (including resend and retry guards).
5. On successful verification, Filament session is completed and user is redirected to panel/dashboard target.
6. Logout returns users to `/login`.

### Supabase-backed API/session flow (retained)

This flow is retained for existing dashboard/session integrations protected by `supabase.auth` middleware.

1. Client calls protected endpoints with `Authorization: Bearer <token>`.
2. Backend validates token with Supabase `/auth/v1/user`.
3. Backend maps email to local `user` table and enforces account/role requirements.

Useful endpoints for testing:

- `/login` (public)
- `/otp` (public guest route; Filament OTP challenge page)
- `/auth/otp/request` (requires bearer token; Supabase-backed API OTP request endpoint)
- `/auth/otp/verify` (requires bearer token; Supabase-backed API OTP verify endpoint)
- `/auth/session` (requires bearer token)
- `/dashboard` (public dashboard shell page; dynamic tabs swap content in-place)
- `/dashboard/content/{tab}` (protected HTML fragment endpoint for dashboard tab content)
- `/admin/ping` (requires bearer token + admin role)
- `/auth/logout` (client-side token clear handoff for Supabase-backed flows)

OTP behavior note:

- OTP delivery is strict email send flow.
- No debug OTP value or fallback delivery hint is exposed by API responses.

Audit note for auth tracking:

- Login-related auth attempts (OTP request, OTP verify outcomes, and logout) are persisted to `audit_log`.
- Admin audit records are reviewed from the Filament **Audit Logs** page at `/admin/audit-logs`.

Current audit event taxonomy:

- Authentication:
    - `AUTH_LOGIN_SUCCESS`
    - `AUTH_LOGIN_FAILED`
    - `AUTH_LOGOUT`
    - `AUTH_SESSION_EXPIRED`
- OTP:
    - `OTP_GENERATED_SENT`
    - `OTP_VERIFIED`
    - `OTP_FAILED`
    - `OTP_EXPIRED`
    - `OTP_RESEND`

Admin dashboard navigation standard:

- Keep dashboard as a single page (`/dashboard`)
- Use in-page async tab loading for section content
- Cache loaded tab fragments in browser session storage
