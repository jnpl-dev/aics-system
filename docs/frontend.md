# Frontend Development Guide

## Stack

- **Backend:** Laravel 11
- **Templating:** Blade
- **Dynamic content loading:** Async Blade fragment requests (`/dashboard/content/{tab}`)
- **Client-side interactivity:** Custom JavaScript modules bundled with Vite
- **Styling:** Tailwind CSS
- **Asset bundler:** Vite (built into Laravel)
- **SPA-like behavior:** single dashboard shell with in-place tab content swapping

## Why This Stack

- Everything is written in PHP — no separate JavaScript framework
- Blade handles server-rendered views and tab fragments
- Custom JS handles dashboard tab loading, caching, and in-tab interactions
- This keeps implementation explicit and compatible with current auth/session flow

> Note: sections below that discuss Livewire/Alpine patterns are retained as legacy reference for possible future migration, but they are **not** the active runtime standard.

## Design Tokens (Mandatory for New UI)

Use this palette and font pairing for all new screens and UI updates:

### Color Palette

- `--dark-emerald: #1F6336ff;`
- `--bright-fern: #3DA814ff;`
- `--porcelain: #F0F3EFff;`

### Font Pairing

- `Inter`
- `Public Sans`

Implementation note:

- Body copy should default to **Inter**.
- Headings should default to **Public Sans**.
- Primary actions should use **bright-fern** with **dark-emerald** hover/active states.

---

## Admin Dashboard Navigation Pattern (Current Standard)

The admin dashboard now uses a **single-page shell** pattern:

- Only one page route is used for admin shell rendering: `/dashboard`
- Sidebar tab clicks do **not** navigate to a new page
- The content area swaps using async requests to `/dashboard/content/{tab}`
- Loaded tab fragments are cached client-side to avoid refetching already loaded sections

### Why we use this

- Prevent full page refreshes when switching dashboard tabs
- Keep sidebar and global dashboard context mounted once
- Reduce unnecessary server rendering and browser work for repeated tab visits

### Implementation contract

1. Main dashboard shell view: `resources/views/admin/dashboard.blade.php`
2. Sidebar tab links carry `data-dashboard-tab` attributes
3. Tab content endpoint returns HTML fragments only
4. Tab partials live under `resources/views/admin/tabs/*.blade.php`
5. Client loader and cache live in `resources/js/auth/supabase-auth.js`

### Caching behavior

- Cache key: `aics_dashboard_tab_cache_v1` (stored in `sessionStorage`)
- First time tab visit: fetch from backend endpoint
- Subsequent visits in the same browser session: load from cache
- In-tab fragment loads (search/filter/pagination/form-driven refreshes) update the active tab cache entry so revisiting a tab reflects the latest fetched fragment state.
- Logout clears both auth token and dashboard tab cache

### Prompting note for future work

When requesting dashboard changes, treat the dashboard as **one page** with **dynamic content regions**. Do not add route-per-tab full-page navigation unless explicitly required.

### Table performance/display standard (Required)

For all large data tables (audit logs, activity feeds, user lists, reports):

- Use server-side pagination by default.
- Default page size should be **20 rows** unless a feature explicitly needs a different size.
- Keep table scrolling inside the table container (not the browser window):
    - wrap tables in a fixed/max-height container
    - use `overflow-auto` (or `overflow-y-auto` + `overflow-x-auto`) on that container
    - keep table headers sticky when practical for readability.

Reason: this keeps the dashboard layout stable, reduces DOM load, and avoids whole-page scroll fatigue under heavy datasets.

---

## Installation

Current runtime setup:

```bash
npm install
npm run dev
```

This configures frontend assets via Vite for Blade + JS modules.

---

## Project Structure

```
resources/
├── views/
│   ├── layouts/
│   │   ├── app.blade.php              ← main authenticated layout
│   │   ├── guest.blade.php            ← public applicant layout
│   │   └── admin.blade.php            ← admin layout
│   ├── components/
│   │   ├── ui/
│   │   │   ├── button.blade.php
│   │   │   ├── input.blade.php
│   │   │   ├── modal.blade.php
│   │   │   ├── table.blade.php
│   │   │   ├── badge.blade.php
│   │   │   ├── alert.blade.php
│   │   │   ├── card.blade.php
│   │   │   └── pagination.blade.php
│   │   ├── application/
│   │   │   ├── status-badge.blade.php
│   │   │   ├── timeline.blade.php
│   │   │   ├── detail-card.blade.php
│   │   │   └── document-list.blade.php
│   │   └── shared/
│   │       ├── navbar.blade.php
│   │       ├── sidebar.blade.php
│   │       └── breadcrumb.blade.php
│   └── livewire/                      ← Livewire component views
│       ├── auth/
│       │   ├── login.blade.php
│       │   └── session-status.blade.php
│       ├── applicant/
│       │   ├── select-category.blade.php
│       │   ├── application-form.blade.php
│       │   └── track-application.blade.php
│       ├── aics-staff/
│       │   ├── application-queue.blade.php
│       │   ├── application-detail.blade.php
│       │   └── code-assignment.blade.php
│       ├── mswd/
│       │   ├── application-queue.blade.php
│       │   ├── application-detail.blade.php
│       │   ├── case-study-upload.blade.php
│       │   └── voucher-form.blade.php
│       ├── mayor/
│       │   ├── application-queue.blade.php
│       │   └── application-detail.blade.php
│       ├── accounting/
│       │   ├── voucher-queue.blade.php
│       │   └── voucher-detail.blade.php
│       ├── treasury/
│       │   ├── voucher-queue.blade.php
│       │   └── cheque-form.blade.php
│       ├── admin/
│       │   ├── user-management.blade.php
│       │   ├── category-management.blade.php
│       │   ├── requirement-management.blade.php
│       │   ├── code-reference-management.blade.php
│       │   ├── audit-log-viewer.blade.php
│       │   └── sms-settings.blade.php
│       ├── reports/
│       │   ├── application-report.blade.php
│       │   ├── financial-report.blade.php
│       │   └── notification-report.blade.php
│       └── shared/
│           ├── dashboard.blade.php
│           └── notification-log.blade.php
app/
└── Livewire/                          ← Livewire component PHP classes
    ├── Auth/
    │   ├── Login.php
    │   └── SessionStatus.php
    ├── Applicant/
    │   ├── SelectCategory.php
    │   ├── ApplicationForm.php
    │   └── TrackApplication.php
    ├── AicsStaff/
    │   ├── ApplicationQueue.php
    │   ├── ApplicationDetail.php
    │   └── CodeAssignment.php
    ├── Mswd/
    │   ├── ApplicationQueue.php
    │   ├── ApplicationDetail.php
    │   ├── CaseStudyUpload.php
    │   └── VoucherForm.php
    ├── Mayor/
    │   ├── ApplicationQueue.php
    │   └── ApplicationDetail.php
    ├── Accounting/
    │   ├── VoucherQueue.php
    │   └── VoucherDetail.php
    ├── Treasury/
    │   ├── VoucherQueue.php
    │   └── ChequeForm.php
    ├── Admin/
    │   ├── UserManagement.php
    │   ├── CategoryManagement.php
    │   ├── RequirementManagement.php
    │   ├── CodeReferenceManagement.php
    │   ├── AuditLogViewer.php
    │   └── SmsSettings.php
    ├── Reports/
    │   ├── ApplicationReport.php
    │   ├── FinancialReport.php
    │   └── NotificationReport.php
    └── Shared/
        ├── Dashboard.php
        └── NotificationLog.php
```

---

## Core Principles

### 1. Livewire for Server-Side Reactivity

Use Livewire whenever the interaction requires data from the database or server-side logic.

```php
// Every Livewire component has a PHP class and a Blade view
class ApplicationQueue extends Component
{
    public string $search = '';
    public string $status = '';

    public function render()
    {
        return view('livewire.aics-staff.application-queue', [
            'applications' => Application::query()
                ->when($this->search, fn($q) =>
                    $q->where('reference_code', 'like', "%{$this->search}%")
                )
                ->when($this->status, fn($q) =>
                    $q->where('status', $this->status)
                )
                ->paginate(15)
        ]);
    }
}
```

```html
<!-- livewire/aics-staff/application-queue.blade.php -->
<div>
    <input
        wire:model.live.debounce.300ms="search"
        placeholder="Search reference code"
    />
    <select wire:model.live="status">
        <option value="">All statuses</option>
        <option value="submitted">Submitted</option>
        <option value="under_review">Under Review</option>
    </select>

    <div wire:loading>Loading...</div>

    <table>
        @foreach($applications as $application)
        <tr>
            <td>{{ $application->reference_code }}</td>
            <td>{{ $application->applicant_last_name }}</td>
            <td>{{ $application->status }}</td>
            <td>
                <a
                    href="/applications/{{ $application->application_id }}"
                    wire:navigate
                >
                    View
                </a>
            </td>
        </tr>
        @endforeach
    </table>

    {{ $applications->links() }}
</div>
```

### 2. Alpine.js for Client-Side Only Interactions

Use Alpine.js whenever the interaction does not need the server — modals, toggles, show/hide, confirmations.

```html
<!-- Confirmation modal before approving -->
<div x-data="{ confirmOpen: false, action: '' }">
    <button @click="confirmOpen = true; action = 'approve'">Approve</button>

    <div x-show="confirmOpen" class="fixed inset-0 bg-black bg-opacity-50">
        <div class="bg-white p-6 rounded">
            <p>Are you sure you want to approve this application?</p>
            <button @click="confirmOpen = false">Cancel</button>
            <button @click="$wire.approve(); confirmOpen = false">
                Confirm
            </button>
        </div>
    </div>
</div>
```

### 3. wire:navigate for SPA-Like Navigation

Add `wire:navigate` to all internal links to get instant SPA-like page transitions.

```html
<!-- Always use wire:navigate on internal links -->
<a href="/applications" wire:navigate>Applications</a>
<a href="/applications/{{ $id }}" wire:navigate>View</a>
```

### 4. Everything is a Component

Never repeat UI markup. Extract repeating elements into Blade components or Livewire components.

### Shared button component standard

Use `resources/views/components/shared/button.blade.php` for all button actions.

Supported variants:

- `primary`
- `secondary`
- `tertiary`

Supported props:

- `variant` (default: `primary`)
- `type` (default: `button`)
- `fullWidth` (default: `false`)
- `loadingText` (optional; shown while button is in loading state)

Loading behavior contract:

- The component includes a built-in spinner (`data-btn-spinner`)
- The component wraps visible label text in `data-btn-label`
- Client logic can toggle loading by setting `data-loading` + `aria-busy`
- If `loadingText` is provided, label swaps to that text while loading

Example:

```blade
<x-shared.button
    id="save-settings-btn"
    type="button"
    variant="primary"
    loading-text="Saving..."
    :full-width="true"
>
    Save Settings
</x-shared.button>
```

```bash
# Create a new Livewire component
php artisan make:livewire AicsStaff/ApplicationQueue

# Create a Blade UI component
php artisan make:component ui/Button
```

### 5. Props Down, Events Up in Livewire

- Pass data to child Livewire components via props
- Use Livewire events to communicate from child to parent

```php
// Child fires event
$this->dispatch('application-approved', applicationId: $this->applicationId);

// Parent listens
#[On('application-approved')]
public function handleApproved($applicationId) { }
```

---

## Livewire Patterns for AICS

### Pattern 1: Live Search Queue

Used for all application and voucher queues.

```php
class ApplicationQueue extends Component
{
    public string $search = '';
    public string $status = '';
    public string $sortBy = 'submitted_at';
    public string $sortDir = 'desc';

    public function sort($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDir = 'asc';
        }
    }

    public function render()
    {
        return view('livewire.aics-staff.application-queue', [
            'applications' => Application::query()
                ->when($this->search, fn($q) =>
                    $q->where('reference_code', 'like', "%{$this->search}%")
                     ->orWhere('applicant_last_name', 'like', "%{$this->search}%")
                )
                ->when($this->status, fn($q) => $q->where('status', $this->status))
                ->orderBy($this->sortBy, $this->sortDir)
                ->paginate(15)
        ]);
    }
}
```

### Pattern 2: Dynamic Requirements Form

Used for application submission — requirements update when category changes.

```php
class ApplicationForm extends Component
{
    public int $category_id = 0;
    public string $relationship = '';
    public array $documents = [];
    public $authorizationLetter;

    public function updatedCategoryId()
    {
        $this->documents = [];
    }

    public function getRequirementsProperty()
    {
        if (!$this->category_id) return collect();
        return Requirement::where('category_id', $this->category_id)
            ->where('is_active', true)
            ->get();
    }

    public function getShowAuthorizationLetterProperty()
    {
        return $this->relationship === 'other';
    }

    public function submit()
    {
        $this->validate([
            'category_id' => 'required|exists:ASSISTANCE_CATEGORY,category_id',
            'relationship' => 'required',
            'documents.*' => 'file|max:5120',
        ]);
        // handle submission
    }

    public function render()
    {
        return view('livewire.applicant.application-form', [
            'categories' => AssistanceCategory::where('is_active', true)->get(),
            'requirements' => $this->requirements,
            'showAuthorizationLetter' => $this->showAuthorizationLetter,
        ]);
    }
}
```

### Pattern 3: Action with Confirmation

Used for approve, reject, forward actions.

```php
class ApplicationDetail extends Component
{
    public Application $application;
    public string $remarks = '';

    public function approve()
    {
        $this->application->update(['status' => 'forwarded_to_mswd']);

        ApplicationLog::create([
            'application_id' => $this->application->application_id,
            'performed_by' => auth()->id(),
            'action' => 'Application approved and forwarded to MSWD',
            'from_status' => 'under_review',
            'to_status' => 'forwarded_to_mswd',
        ]);

        $this->dispatch('action-completed', message: 'Application approved successfully');
    }

    public function reject()
    {
        $this->validate(['remarks' => 'required|string']);

        $this->application->update(['status' => 'rejected']);

        ApplicationLog::create([
            'application_id' => $this->application->application_id,
            'performed_by' => auth()->id(),
            'action' => 'Application rejected',
            'from_status' => 'under_review',
            'to_status' => 'rejected',
            'remarks' => $this->remarks,
        ]);

        $this->dispatch('action-completed', message: 'Application rejected');
    }

    public function render()
    {
        return view('livewire.aics-staff.application-detail');
    }
}
```

### Pattern 4: File Upload

Used for documents, social case study, allotment slip.

```php
class CaseStudyUpload extends Component
{
    #[Validate('required|file|mimes:pdf,jpg,jpeg,png|max:10240')]
    public $caseStudyFile;

    public Application $application;

    public function upload()
    {
        $this->validate();

        $path = $this->caseStudyFile->store('case-studies', 'private');

        SocialCaseStudy::create([
            'application_id' => $this->application->application_id,
            'conducted_by' => auth()->id(),
            'file_name' => $this->caseStudyFile->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $this->caseStudyFile->getSize(),
            'mime_type' => $this->caseStudyFile->getMimeType(),
        ]);

        $this->dispatch('upload-complete');
    }

    public function render()
    {
        return view('livewire.mswd.case-study-upload');
    }
}
```

### Pattern 5: Application Timeline

Used to display ApplicationLog as parcel tracking.

```php
class ApplicationTimeline extends Component
{
    public int $applicationId;

    public function render()
    {
        return view('livewire.shared.application-timeline', [
            'logs' => ApplicationLog::where('application_id', $this->applicationId)
                ->with('performedBy')
                ->orderBy('timestamp', 'asc')
                ->get()
        ]);
    }
}
```

```html
<!-- livewire/shared/application-timeline.blade.php -->
<div>
    @foreach($logs as $log)
    <div class="flex gap-4">
        <div class="flex flex-col items-center">
            <div class="w-3 h-3 rounded-full bg-blue-500"></div>
            @if(!$loop->last)
            <div class="w-0.5 h-full bg-gray-200"></div>
            @endif
        </div>
        <div class="pb-6">
            <p class="font-medium">{{ $log->action }}</p>
            <p class="text-sm text-gray-500">
                {{ $log->performedBy?->first_name ?? 'System' }} · {{
                $log->timestamp->diffForHumans() }}
            </p>
            @if($log->remarks)
            <p class="text-sm mt-1">{{ $log->remarks }}</p>
            @endif
        </div>
    </div>
    @endforeach
</div>
```

---

## Alpine.js Patterns for AICS

### Modal Pattern

```html
<div x-data="{ open: false }">
    <button @click="open = true">Open</button>
    <div x-show="open" x-trap="open" class="fixed inset-0 z-50">
        <div
            class="absolute inset-0 bg-black opacity-50"
            @click="open = false"
        ></div>
        <div class="relative bg-white rounded p-6 max-w-md mx-auto mt-20">
            <button @click="open = false">Close</button>
            <slot />
        </div>
    </div>
</div>
```

### Confirm Before Action Pattern

```html
<div x-data="{ confirm: false }">
    <button @click="confirm = true">Approve</button>
    <div x-show="confirm">
        <p>Are you sure?</p>
        <button @click="confirm = false">Cancel</button>
        <button @click="$wire.approve()">Confirm</button>
    </div>
</div>
```

### Tabs Pattern

```html
<div x-data="{ tab: 'info' }">
    <button
        @click="tab = 'info'"
        :class="tab === 'info' ? 'border-b-2 border-blue-500' : ''"
    >
        Information
    </button>
    <button
        @click="tab = 'documents'"
        :class="tab === 'documents' ? 'border-b-2 border-blue-500' : ''"
    >
        Documents
    </button>
    <div x-show="tab === 'info'">Application info here</div>
    <div x-show="tab === 'documents'">Documents here</div>
</div>
```

### Toast Notification Pattern

```html
<div
    x-data="{ show: false, message: '' }"
    x-on:action-completed.window="show = true; message = $event.detail.message; setTimeout(() => show = false, 3000)"
>
    <div
        x-show="show"
        class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded"
    >
        <span x-text="message"></span>
    </div>
</div>
```

---

## Required States for Every Dynamic Component

Every Livewire component that fetches data must handle all three states:

```html
<!-- Loading state -->
<div wire:loading>
    <div class="animate-pulse">Loading...</div>
</div>

<!-- Empty state -->
<div wire:loading.remove>
    @if($items->isEmpty())
    <div class="text-center py-12 text-gray-500">No records found.</div>
    @else
    <!-- render items -->
    @endif
</div>

<!-- Error state — handle in PHP -->
```

---

## Application Status Badge Colors

Use these Tailwind classes consistently across all role dashboards:

```php
// In a Blade component or helper
function statusColor(string $status): string {
    return match($status) {
        'submitted'                => 'bg-blue-100 text-blue-800',
        'under_review'             => 'bg-yellow-100 text-yellow-800',
        'forwarded_to_mswd'        => 'bg-yellow-100 text-yellow-800',
        'pending_additional_docs'  => 'bg-orange-100 text-orange-800',
        'approved_by_mswd'         => 'bg-teal-100 text-teal-800',
        'coding'                   => 'bg-yellow-100 text-yellow-800',
        'forwarded_to_mayor'       => 'bg-yellow-100 text-yellow-800',
        'approved_by_mayor'        => 'bg-teal-100 text-teal-800',
        'voucher_preparation'      => 'bg-yellow-100 text-yellow-800',
        'forwarded_to_accounting'  => 'bg-yellow-100 text-yellow-800',
        'forwarded_to_treasury'    => 'bg-yellow-100 text-yellow-800',
        'cheque_ready'             => 'bg-green-100 text-green-800',
        'claimed'                  => 'bg-gray-100 text-gray-800',
        'on_hold'                  => 'bg-orange-100 text-orange-800',
        'rejected'                 => 'bg-red-100 text-red-800',
        default                    => 'bg-gray-100 text-gray-800',
    };
}
```

---

## Accessibility and UX Rules

- All form fields must have visible labels — never placeholder only
- Required fields must be marked with asterisk
- Error messages must be descriptive — not just "Invalid input"
- Use `wire:loading` to show feedback for any action taking more than 300ms
- Destructive actions must require Alpine.js confirmation dialog
- Success and error feedback must use toast notifications via Alpine.js events
- Tables must be responsive or horizontally scrollable on small screens
- File uploads must show accepted file types and maximum file size
- Always use `wire:navigate` on internal links for SPA-like navigation

---

## Artisan Commands for This Stack

```bash
# Create a new Livewire component
php artisan make:livewire ComponentName

# Create a Blade UI component
php artisan make:component ComponentName

# Create a new Laravel model
php artisan make:model ModelName

# Create a controller
php artisan make:controller ControllerName

# Run migrations
php artisan migrate

# Clear all caches
php artisan optimize:clear
```

---

## Change Log

| Date | Change                                        | Updated By |
| ---- | --------------------------------------------- | ---------- |
|      | Initial frontend guide created for TALL stack |            |
