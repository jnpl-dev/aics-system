# API Reference

## Status
Partially implemented. This document reflects currently available auth/admin endpoints and dashboard fragment endpoints.

## Conventions

- The project currently uses **mixed response types**:
  - JSON responses for auth/session/OTP/login-attempt/admin ping endpoints
  - HTML fragment responses for dashboard tab content (`/dashboard/content/{tab}`)
  - Redirect responses for form-style admin actions (e.g., user creation)
- Protected endpoints require `Authorization: Bearer <token>` and pass through `supabase.auth` middleware.
- Validation failures:
  - JSON endpoints: HTTP 422 with validation payload
  - Form endpoints: redirect back with session errors

## Implemented Endpoints

### Public

- `GET /login` → login page
- `GET /dashboard` → dashboard shell page
- `POST /auth/login-attempt` → records login attempt and lockout signals
- `POST /auth/login-cooldown-check` → checks current lockout cooldown

### Authenticated (`supabase.auth`)

- `GET /auth/session` → validates token + returns resolved local user
- `POST /auth/otp/request` → requests 6-digit OTP email
- `POST /auth/otp/verify` → verifies OTP
- `GET /auth/logout` → clears server-side OTP verification state

### Admin (`supabase.auth` + role middleware)

- `GET /admin/ping` (`role:admin`) → admin-access check
- `GET /dashboard/content/{tab}` (`role:admin,system_admin`) → HTML fragment for dashboard tab
- `POST /admin/users` (`role:admin,system_admin`) → create user (redirect + validation errors in session)