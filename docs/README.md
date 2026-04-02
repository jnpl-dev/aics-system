# AICS Digital Application and Notification System

## Overview

A web-based system for the Assistance to Individuals in Crisis Situation (AICS) program of a local government unit in the Philippines. The system digitizes the application, review, approval, and notification process for financial assistance.

## Purpose

- Allow applicants to submit assistance applications digitally or through staff-assisted walk-in submission
- Track applications through a multi-stage approval workflow
- Automate SMS notifications at every key stage
- Generate reports for management oversight

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
    - `--dark-emerald: #1F6336ff`
    - `--bright-fern: #3DA814ff`
    - `--porcelain: #F0F3EFff`
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

Current implemented flow:

1. User opens `/login` and signs in using Supabase email/password.
2. Browser stores access token in local storage key `aics_supabase_access_token`.
3. Browser requests a 6-digit OTP from `/auth/otp/request` using the bearer token.
4. User submits OTP code to `/auth/otp/verify`.
5. Browser calls backend `/auth/session` with `Authorization: Bearer <token>`.
6. Backend validates token with Supabase `/auth/v1/user`, maps email to local `user` table, and enforces OTP completion.
7. Protected pages use middleware:
    - `supabase.auth` for token + account validation
    - `role:*` for role-specific route guards

Useful endpoints for testing:

- `/login` (public)
- `/auth/otp/request` (requires bearer token; sends 6-digit OTP to email)
- `/auth/otp/verify` (requires bearer token; verifies OTP and unlocks protected routes)
- `/auth/session` (requires bearer token)
- `/dashboard` (public dashboard shell page; dynamic tabs swap content in-place)
- `/dashboard/content/{tab}` (protected HTML fragment endpoint for dashboard tab content)
- `/admin/ping` (requires bearer token + admin role)
- `/auth/logout` (client-side token clear handoff)

OTP behavior note:

- OTP delivery is strict email send flow.
- No debug OTP value or fallback delivery hint is exposed by API responses.

Audit note for auth tracking:

- Login-related auth attempts (OTP request, OTP verify outcomes, and logout) are persisted to `audit_log`.
- The admin **Audit Log** dashboard tab now displays these authentication records.

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
