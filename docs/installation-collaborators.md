# Collaborator Installation Guide (Local Setup)

This guide is for contributors who need to run `aics-system` on a local machine.

## 1) Prerequisites

Install these first:

- **Git**
- **PHP 8.2+** (project requires `^8.2`)
- **Composer 2+**
- **Node.js 20+** and **npm**
- **MySQL 8+** (or compatible)

Required PHP extensions:

- `intl` (important for Filament export notifications)
- `pdo_mysql`
- `mbstring`
- `openssl`
- `tokenizer`
- `xml`
- `ctype`
- `json`
- `fileinfo`

> If Composer or Filament throws intl-related runtime errors, check that the **active CLI PHP** has `extension=intl` enabled.

---

## 2) Clone the repository

```powershell
git clone https://github.com/jnpl-dev/aics-system.git
cd aics-system
```

---

## 3) Install backend and frontend dependencies

```powershell
composer install
npm install
```

---

## 4) Configure environment file

Copy `.env.example` to `.env` and update values.

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

Minimum required `.env` values:

- App
    - `APP_NAME`
    - `APP_ENV=local`
    - `APP_URL=http://localhost:8000` (or your preferred local URL)
- Database
    - `DB_CONNECTION=mysql`
    - `DB_HOST`
    - `DB_PORT`
    - `DB_DATABASE`
    - `DB_USERNAME`
    - `DB_PASSWORD`
- Runtime stores (default project behavior)
    - `CACHE_STORE=database`
    - `SESSION_DRIVER=database`
    - `QUEUE_CONNECTION=database`

Supabase-related values (needed for full auth/storage behavior):

- `SUPABASE_URL`
- `SUPABASE_ANON_KEY`
- `SUPABASE_SERVICE_ROLE_KEY`
- `SUPABASE_JWT_ISSUER`
- `SUPABASE_JWKS_URL`
- Storage values from `.env.example` if using Supabase file storage.

---

## 5) Prepare database

Create your local DB (example name: `aics_system`), then run migrations:

```powershell
php artisan migrate
```

If you need a clean DB during development:

```powershell
php artisan migrate:fresh
```

---

## 6) Build frontend assets

```powershell
npm run build
```

For active development watch mode (optional):

```powershell
npm run dev
```

---

## 7) Run the app locally

Basic local run:

```powershell
php artisan serve
```

Open app:

- `http://127.0.0.1:8000/login`

---

## 8) Run queue worker (recommended in local)

Exports and other queued jobs may rely on the queue worker.

```powershell
php artisan queue:listen --tries=1 --timeout=0
```

If you skip this while using async exports, export completion may be delayed.

---

## 9) Optional scheduler for export retention

Project schedules pruning of stale export artifacts (`exports:prune --days=14`).

For local testing of scheduled tasks:

```powershell
php artisan schedule:work
```

---

## 10) Verify your setup

Run tests:

```powershell
php artisan test
```

Useful focused checks:

```powershell
php artisan test --filter=FilamentUserManagementTest
php artisan test --filter=FilamentAicsStaffApplicationsTest
```

---

## 11) First-time contributor checklist

- [ ] `composer install` completed without missing extension errors
- [ ] `.env` configured correctly
- [ ] `php artisan migrate` ran successfully
- [ ] `npm run build` completed
- [ ] Can open `/login`
- [ ] Queue worker runs when testing exports
- [ ] `php artisan test` passes locally

---

## 12) Common issues and fixes

### A) Filament/export runtime error mentioning formatting or intl

Cause: `ext-intl` disabled in active PHP CLI.

Fix:

1. Enable `extension=intl` in active `php.ini`.
2. Restart terminal.
3. Re-run `php -m` and confirm `intl` appears.

### B) App is slow or stale after data cleanup

Cause: DB cache/session tables still contain old keys.

Fix:

```powershell
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### C) Exports do not finish

Cause: queue worker not running.

Fix: run queue listener in a separate terminal.

### D) Test/sample records pollute dashboards

Cause: local tests inserted synthetic records.

Fix: clean those rows and clear cache before demos.

---

## Notes for maintainers

- Current cache strategy intentionally favors:
    - **cached static UI options** (export modal disk options, admin new-user option sets)
    - **live-rendered frequently changing analytics** (to avoid stale operator metrics)
- If you change option labels/values, clear cache once so collaborators get refreshed static keys.
