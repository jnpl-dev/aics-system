# Progress Tracker

## Current Phase

Phase 2: Authentication and Access Management

## Completed Tasks

- [x] Initialize project repository
- [x] Set up version control (Git)
- [x] Choose and set up tech stack
- [x] Set up development environment
- [x] Set up database (MySQL)
- [x] Run CREATE TABLE queries to initialize schema
- [x] Set up environment variables file (.env)
- [x] Set up project folder structure
- [x] Connect Project to Database

## In Progress

- [ ] Implement password reset flow

## Upcoming Phases

### Phase 1: Project Setup

- [x] Initialize project repository
- [x] Set up version control (Git)
- [x] Choose and set up tech stack
- [x] Set up development environment
- [x] Set up database (MySQL)
- [x] Run CREATE TABLE queries to initialize schema
- [x] Set up environment variables file (.env)
- [x] Set up project folder structure
- [x] Connect Project to Database

### Phase 2: Authentication and Access Management

- [x] Implement user registration (admin creates accounts only)
- [x] Implement login with email and password
- [x] Implement password hashing (bcrypt or Argon2)
- [x] Implement JWT or session-based authentication
- [x] Implement MFA / OTP verification
- [x] Implement role-based access control (RBAC) middleware
- [ ] Implement password reset flow
- [x] Implement login attempt tracking
- [x] Implement logout
- [x] Implement route guards per role

### Phase 3: System Administration

- [x] Build user management page
- [ ] Build role and permission assignment
- [ ] Build password reset for users
- [ ] Build SMS configuration page
- [x] Build audit log viewer
- [ ] Build system activity monitor

### Phase 4: Assistance Category and Requirements Management

- [ ] Build assistance category list page (CRUD)
- [ ] Build requirements list page per category (CRUD)
- [ ] Implement is_active toggle for categories and requirements
- [ ] Implement dynamic requirements checklist based on selected category

### Phase 5: Application Submission Management

- [ ] Build assistance type selection page
- [ ] Build dynamic requirements display
- [ ] Build application form with applicant and beneficiary fields
- [ ] Implement authorization letter logic in code
- [ ] Build document upload functionality per requirement
- [ ] Implement reference code generation on submission
- [ ] Build application tracking page
- [ ] Build application status page with ApplicationLog timeline
- [ ] Implement staff-assisted submission
- [ ] Build resubmission flow

### Phase 6: Application Review and Validation Management

- [ ] Build application queue for AICS Staff
- [ ] Build application detail view with all documents
- [ ] Implement document validation workflow
- [ ] Implement approval / rejection / resubmission request actions
- [ ] Build feedback remarks form
- [ ] Implement ApplicationReview record creation
- [ ] Implement ApplicationLog entry creation
- [ ] Build forward to MSWD Officer functionality
- [ ] Implement auto-SMS notification trigger

### Phase 7: Social Case Study and Assistance Code Management

- [ ] Build application queue for MSWD Officer
- [ ] Build application detail view for MSWD Officer
- [ ] Implement further review and interview workflow
- [ ] Build additional document request functionality
- [ ] Build social case study file upload
- [ ] Implement approval and return to AICS Staff workflow
- [ ] Build assistance code assignment page
- [ ] Implement CodeReference lookup
- [ ] Build code assignment form
- [ ] Build forward to Mayor's Office functionality
- [ ] Implement auto-SMS notification trigger

### Phase 8: Mayor's Office Review

- [ ] Build application queue for Mayor's Office Staff
- [ ] Build application detail view
- [ ] Implement approval workflow
- [ ] Implement code adjustment request workflow
- [ ] Build ApplicationReview record creation
- [ ] Implement return to MSWD on approval
- [ ] Implement auto-SMS notification trigger

### Phase 9: Financial Assistance Management

- [ ] Build voucher preparation page for MSWD Officer
- [ ] Implement voucher creation
- [ ] Build forward to Accounting Office functionality
- [ ] Build voucher review page for Accountant
- [ ] Implement financial decision workflow
- [ ] Implement voucher adjustment request loop
- [ ] Build forward to Treasurer's Office functionality
- [ ] Build voucher review page for Treasurer
- [ ] Implement fund availability check
- [ ] Implement on hold functionality
- [ ] Build cheque preparation page
- [ ] Build allotment slip upload functionality
- [ ] Implement auto-SMS notification to applicant
- [ ] Build cheque claim confirmation
- [ ] Implement ApplicationLog entries

### Phase 10: Notification Management

- [ ] Integrate SMS API provider
- [ ] Implement auto-SMS triggers at all key stages
- [ ] Implement delivery status tracking
- [ ] Build notification log viewer

### Phase 11: Reporting and Monitoring

- [ ] Build application summary report
- [ ] Build financial report
- [ ] Build SMS notification log report
- [ ] Build audit log report
- [ ] Implement print functionality
- [ ] Build main dashboard with key metrics per role

### Phase 12: Testing

- [ ] Unit test all business logic functions
- [ ] Test all role-based access controls
- [ ] Test application workflow end-to-end
- [ ] Test all SMS notification triggers
- [ ] Test document upload and retrieval
- [ ] Test reference code tracking
- [ ] Test all adjustment request loops
- [ ] Test resubmission flow
- [ ] Test on hold and fund availability flow
- [ ] Performance test
- [ ] Security test

### Phase 13: Deployment

- [ ] Set up production server
- [ ] Set up production database
- [ ] Configure environment variables
- [ ] Set up SSL certificate
- [ ] Set up database backups
- [ ] Deploy application
- [ ] Smoke test all features
- [ ] Train staff on system usage

## Change Log

| Date       | Change                                                                                                                                                                                                                                                                                                    | Updated By |
| ---------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ---------- |
|            | Initial setup                                                                                                                                                                                                                                                                                             |            |
| 2026-03-30 | Documentation alignment completed: canonical application statuses, workflow transitions, and status-to-SMS trigger mapping standardized before development                                                                                                                                                | Copilot    |
| 2026-03-30 | Connected Laravel to MySQL database `aics_system` and added serve-time terminal DB connection status message                                                                                                                                                                                              | Copilot    |
| 2026-03-30 | Ran Laravel migrations on MySQL `aics_system`; created missing `sessions` table and resolved SQLSTATE[42S02] sessions error                                                                                                                                                                               | Copilot    |
| 2026-03-30 | Phase 1 checklist normalized and marked complete after verification (git initialized, env configured, MySQL connected, migrations ran, tests passing)                                                                                                                                                     | Copilot    |
| 2026-03-31 | Cleaned unused rollback artifacts (orphan Livewire auth/dashboard files, layouts, chart module, and prototype component files) to keep the workspace tidy                                                                                                                                                 | Copilot    |
| 2026-03-31 | Added Supabase Auth setup scaffolding (env/config, `supabase.auth` token middleware, `role` RBAC middleware, middleware tests, and docs usage examples)                                                                                                                                                   | Copilot    |
| 2026-03-31 | Implemented Supabase login integration slice (`/login`, `/auth/session`, `/dashboard`, `/admin/ping`, `/auth/logout`) with frontend flow and feature tests                                                                                                                                                | Copilot    |
| 2026-03-31 | Added email OTP MFA flow (`/mfa`, OTP request/verify endpoints, MFA middleware enforcement, and updated auth integration tests)                                                                                                                                                                           | Copilot    |
| 2026-03-31 | Reverted authentication to simple Supabase email/password flow and removed active MFA route/UI flow from runtime and docs.                                                                                                                                                                                | Copilot    |
| 2026-03-31 | Refactored admin dashboard to single-page dynamic tab loading with cached tab fragments (`sessionStorage`) and documented the dashboard navigation standard.                                                                                                                                              | Copilot    |
| 2026-03-31 | Finalized password hashing using Laravel hashed cast (bcrypt/Argon2 compatible), aligned user factory fields, and added hashing behavior unit tests.                                                                                                                                                      | Copilot    |
| 2026-03-31 | Implemented mandatory 6-digit email OTP verification for all roles after password login, with protected route enforcement and OTP integration tests.                                                                                                                                                      | Copilot    |
| 2026-03-31 | Removed temporary OTP fallback/debug response hints and aligned docs to strict email-only OTP delivery behavior.                                                                                                                                                                                          | Copilot    |
| 2026-03-31 | Improved login OTP UX: immediate transition to OTP step after password auth (while OTP email request continues), with guarded pending-action states.                                                                                                                                                      | Copilot    |
| 2026-03-31 | Added reusable `x-shared.button` component with `primary`/`secondary`/`tertiary` variants and integrated it into login + OTP actions.                                                                                                                                                                     | Copilot    |
| 2026-03-31 | Added button loading indicators: built-in spinner plus optional `loadingText` label swap during async operations (sign in, verify, resend).                                                                                                                                                               | Copilot    |
| 2026-04-01 | Implemented authentication login-attempt tracking with persisted `audit_log` records and rendered those records in the Audit Log dashboard tab.                                                                                                                                                           | Copilot    |
| 2026-04-01 | Refined audit event taxonomy with explicit auth/OTP event codes, added failed login capture endpoint, and recorded session-expired events.                                                                                                                                                                | Copilot    |
| 2026-04-01 | Added lockout policy for authentication: 5 failed login attempts trigger a 15-minute cooldown, with backend cooldown-check endpoint and frontend pre-check.                                                                                                                                               | Copilot    |
| 2026-04-01 | Upgraded Audit Log table UX/performance: 20-row server pagination, internal scroll container, sticky headers, and in-tab AJAX pagination navigation.                                                                                                                                                      | Copilot    |
| 2026-04-01 | Fixed dashboard audit “View latest” and stale fragment cache behavior by normalizing fragment links and bumping tab cache version with legacy cache cleanup.                                                                                                                                              | Copilot    |
| 2026-04-01 | Built the User Management admin tab with searchable/filterable user list, 20-row pagination, and in-tab fragment form/pagination loading.                                                                                                                                                                 | Copilot    |
| 2026-04-01 | Implemented admin Add User backend flow (`/admin/users`) with Laravel `Password` rule validation, server-side sanitization, and feature tests for success/failure.                                                                                                                                        | Copilot    |
| 2026-04-01 | Standardized Add User modal field spacing rhythm and preserved compact control height for consistency with filter window spacing conventions.                                                                                                                                                             | Copilot    |
| 2026-04-02 | Completed Add User end-to-end flow: dashboard modal now submits with bearer-authenticated AJAX, returns JSON validation/success responses, refreshes User Management tab, persists users to DB, and records user creation in `audit_log` as `create` action (verified by feature tests + frontend build). | Copilot    |
| 2026-04-02 | Fixed User Management tab nested-form markup that could intercept Add User submission; Add User modal form is now isolated from filter form so submit reliably reaches `/admin/users` and refreshes the tab list.                                                                                         | Copilot    |
| 2026-04-02 | Fixed live Add User persistence blocker by aligning MySQL `user.role` enum with documented RBAC roles (`mayor_office_staff`, `system_admin`), which prevented inserts for those roles due SQL enum mismatch.                                                                                              | Copilot    |
| 2026-04-02 | Unified `admin` and `system_admin` behavior: role middleware now normalizes `system_admin` to `admin`, protected admin routes use canonical `role:admin`, Add User role select now shows a single Admin role, and docs were aligned to alias semantics.                                                   | Copilot    |
| 2026-04-02 | Fixed new-account login failure by provisioning Supabase Auth credentials during `/admin/users` creation (with graceful error handling and rollback cleanup), ensuring newly created accounts can authenticate successfully.                                                                              | Copilot    |
| 2026-04-02 | Added `docs/troubleshooting.md` incident log covering Add User modal no-save issue, role enum mismatch, invalid login for newly created users, and Supabase endpoint test-fake failures, including root causes and repeatable fixes.                                                                      | Copilot    |
| 2026-04-02 | Performed cleanup pass on auth/user management code: removed unused exception variables, removed non-essential unused Supabase endpoint fakes in unrelated tests, fixed controller indentation, and re-verified tests/build.                                                                              | Copilot    |
| 2026-04-02 | Added User Management row-level Actions UI: new `Actions` column with per-user kebab menu (View Details, Edit User submenu for Reset Password and Activate/Deactivate, Delete User) and reusable action window components for each flow.                                                                  | Copilot    |
| 2026-04-02 | Refined User Management/Audit Log dashboard UX: flattened excess shell spacing, restored single-card tab presentation, tightened header-to-table spacing, and tuned table viewport windows for compact in-card scrolling.                                                                                 | Copilot    |
| 2026-04-02 | Finalized dashboard tab fragment caching behavior: tab content now reuses cache on revisit while in-tab fragment updates (search/filter/pagination) synchronize the active tab cache to prevent stale renders without hard refresh.                                                                       | Copilot    |
| 2026-04-03 | Started Filament migration track: installed Filament v5 admin panel, registered `AdminPanelProvider`, added panel access gating in `User` model (`active` + `admin/system_admin`), and scaffolded first Filament `UserResource` with working list/create/edit routes under `/admin/users/*`.              | Copilot    |
| 2026-04-03 | Migration environment note: local PHP `ext-intl` is still required for clean Composer installs without temporary platform-ignore flags.                                                                                                                                                                   | Copilot    |
| 2026-04-03 | Began login migration (step 1): customized Filament admin login page (`App\Filament\Pages\Auth\Login`) with AICS copy, normalized email credential handling, and explicit active-admin failure messaging; retained legacy Supabase `/login` flow to avoid breaking current `/dashboard` token-based auth. | Copilot    |
| 2026-04-03 | Completed login migration (step 2 bridge): `/login` now redirects to Filament admin login (`/admin/login`), legacy Supabase login moved to `/login/legacy`, and legacy frontend auth redirects were updated to target `/login/legacy` to preserve existing token-based dashboard flows during transition. | Copilot    |
| 2026-04-03 | Completed login migration (step 3 full replacement): legacy `/login/legacy` route removed, Filament login now performs email OTP confirmation before session sign-in, and panel access policy was widened to all `active` users with canonical entrypoint at `/login`.                                    | Copilot    |
| 2026-04-03 | Finalized Filament auth UX: dedicated `/otp` challenge page (separate from credentials page), six-digit OTP box inputs, verify loading spinner, disabled controls until code is sent, and toast notifications for OTP send/resend/verified states.                                                        | Copilot    |
| 2026-04-03 | Canonicalized auth redirects: Filament unauthenticated + logout responses now route to `/login` (instead of `/admin/login`).                                                                                                                                                                              | Copilot    |
| 2026-04-03 | Removed obsolete transitional auth artifacts from runtime: legacy `showLogin()` action, old auth login Blade, legacy OTP Blade, and temporary OTP controller.                                                                                                                                             | Copilot    |
