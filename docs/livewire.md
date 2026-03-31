# Livewire Component Reference

## Overview

Every dynamic page in AICS is a Livewire component. This file lists all components, their purpose, their properties, and their actions.

## Component Naming Convention

- PHP class: PascalCase — `ApplicationQueue`
- Blade view: kebab-case — `application-queue.blade.php`
- Folder structure mirrors role — `AicsStaff/ApplicationQueue`

## Create a Component

```bash
php artisan make:livewire AicsStaff/ApplicationQueue
```

This creates:

- `app/Livewire/AicsStaff/ApplicationQueue.php`
- `resources/views/livewire/aics-staff/application-queue.blade.php`

---

## Component Registry

### Auth

| Component          | Purpose                                         |
| ------------------ | ----------------------------------------------- |
| Auth/Login         | Login form with email and password              |
| Auth/SessionStatus | Displays authenticated session and role context |

### Applicant

| Component                  | Purpose                                                          |
| -------------------------- | ---------------------------------------------------------------- |
| Applicant/SelectCategory   | Assistance type selection with requirements preview              |
| Applicant/ApplicationForm  | Full application form with dynamic requirements and file uploads |
| Applicant/TrackApplication | Track application status by reference code and surname           |

### AICS Staff

| Component                   | Purpose                                                      |
| --------------------------- | ------------------------------------------------------------ |
| AicsStaff/ApplicationQueue  | Live search and filter application queue                     |
| AicsStaff/ApplicationDetail | Full application view with approve, reject, resubmit actions |
| AicsStaff/CodeAssignment    | Assign assistance code from CodeReference lookup             |

### MSWD Officer

| Component              | Purpose                                                                 |
| ---------------------- | ----------------------------------------------------------------------- |
| Mswd/ApplicationQueue  | Application queue forwarded from AICS Staff                             |
| Mswd/ApplicationDetail | Application view with case study upload and additional document request |
| Mswd/CaseStudyUpload   | Upload social case study file                                           |
| Mswd/VoucherForm       | Prepare voucher based on approved assistance code                       |

### Mayor's Office

| Component               | Purpose                                                           |
| ----------------------- | ----------------------------------------------------------------- |
| Mayor/ApplicationQueue  | Application queue forwarded from MSWD                             |
| Mayor/ApplicationDetail | Application view with approve and code adjustment request actions |

### Accounting

| Component                | Purpose                                                  |
| ------------------------ | -------------------------------------------------------- |
| Accounting/VoucherQueue  | Voucher queue with live search and filter                |
| Accounting/VoucherDetail | Voucher view with approve and adjustment request actions |

### Treasury

| Component             | Purpose                                                               |
| --------------------- | --------------------------------------------------------------------- |
| Treasury/VoucherQueue | Approved voucher queue from Accounting                                |
| Treasury/ChequeForm   | Prepare cheque with allotment slip upload and fund availability check |

### Admin

| Component                     | Purpose                                     |
| ----------------------------- | ------------------------------------------- |
| Admin/UserManagement          | CRUD for user accounts with role assignment |
| Admin/CategoryManagement      | CRUD for assistance categories              |
| Admin/RequirementManagement   | CRUD for requirements per category          |
| Admin/CodeReferenceManagement | Manage codes A-F with amounts               |
| Admin/AuditLogViewer          | View and filter audit logs                  |
| Admin/SmsSettings             | Edit auto-SMS message templates             |

### Reports

| Component                  | Purpose                                                   |
| -------------------------- | --------------------------------------------------------- |
| Reports/ApplicationReport  | Application summary report with date and category filters |
| Reports/FinancialReport    | Financial report for vouchers and cheques                 |
| Reports/NotificationReport | SMS notification log report                               |

### Shared

| Component                  | Purpose                                  |
| -------------------------- | ---------------------------------------- |
| Shared/Dashboard           | Role-specific dashboard with key metrics |
| Shared/ApplicationTimeline | ApplicationLog parcel-tracking timeline  |
| Shared/NotificationLog     | SMS notification log viewer              |

---

## Key Livewire Directives Reference

| Directive                        | Purpose                                                    |
| -------------------------------- | ---------------------------------------------------------- |
| `wire:model`                     | Two-way data binding                                       |
| `wire:model.live`                | Real-time binding — updates on every keystroke             |
| `wire:model.live.debounce.300ms` | Real-time binding with 300ms delay — use for search inputs |
| `wire:model.blur`                | Updates only when input loses focus                        |
| `wire:click`                     | Call a Livewire method on click                            |
| `wire:submit`                    | Call a Livewire method on form submit                      |
| `wire:loading`                   | Show element while Livewire is processing                  |
| `wire:loading.remove`            | Hide element while Livewire is processing                  |
| `wire:navigate`                  | SPA-like navigation — add to all internal links            |
| `wire:poll`                      | Auto-refresh component every N seconds                     |
| `wire:key`                       | Unique key for list items                                  |
| `$wire.method()`                 | Call Livewire method from Alpine.js                        |
| `$wire.property`                 | Access Livewire property from Alpine.js                    |

---

## Rules

- Every Livewire component must have a corresponding view file
- Never put business logic in the view — all logic goes in the PHP class
- Always validate input before processing in Livewire actions
- Always create ApplicationLog entry after every status change action
- Always dispatch a notification event after every status change action
- Always add wire:navigate to all internal links
- Use wire:loading on all action buttons to prevent double submission
- Never expose sensitive data in public Livewire properties
