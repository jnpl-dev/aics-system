# Tailwind CSS Guide

## Overview
Tailwind CSS is a utility-first CSS framework. Instead of writing custom CSS you compose styles using predefined utility classes directly in your HTML. There is no separate CSS file to maintain for component styles.

## Setup
Tailwind is installed automatically via Laravel Breeze. The config file is at `tailwind.config.js` in your project root.

## Key Configuration for AICS
```js
// tailwind.config.js
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './app/Livewire/**/*.php',
    ],
    theme: {
        extend: {
            colors: {
                primary: {
                    50:  '#eff6ff',
                    100: '#dbeafe',
                    500: '#3b82f6',
                    600: '#2563eb',
                    700: '#1d4ed8',
                    900: '#1e3a5f',
                }
            }
        }
    }
}
```

---

## Design System

### Typography
```html
<!-- Page title -->
<h1 class="text-2xl font-bold text-gray-900">Applications</h1>

<!-- Section heading -->
<h2 class="text-lg font-semibold text-gray-800">Applicant Information</h2>

<!-- Label -->
<label class="text-sm font-medium text-gray-700">Reference Code</label>

<!-- Body text -->
<p class="text-sm text-gray-600">Description text here</p>

<!-- Muted text -->
<p class="text-xs text-gray-400">Last updated 2 hours ago</p>
```

### Buttons
```html
<!-- Primary action -->
<button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
    Approve
</button>

<!-- Secondary action -->
<button class="bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
    Cancel
</button>

<!-- Danger action -->
<button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
    Reject
</button>

<!-- Disabled state -->
<button
    class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium opacity-50 cursor-not-allowed"
    wire:loading.attr="disabled"
>
    <span wire:loading.remove>Submit</span>
    <span wire:loading>Processing...</span>
</button>
```

### Form Inputs
```html
<!-- Text input -->
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">
        Last Name <span class="text-red-500">*</span>
    </label>
    <input
        type="text"
        wire:model="applicant_last_name"
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        placeholder="Enter last name"
    />
    @error('applicant_last_name')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>

<!-- Select -->
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">
        Assistance Type <span class="text-red-500">*</span>
    </label>
    <select
        wire:model.live="category_id"
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
    >
        <option value="">Select type</option>
        @foreach($categories as $category)
            <option value="{{ $category->category_id }}">{{ $category->name }}</option>
        @endforeach
    </select>
</div>

<!-- File input -->
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">
        Upload Document <span class="text-red-500">*</span>
    </label>
    <input
        type="file"
        wire:model="document"
        accept=".pdf,.jpg,.jpeg,.png"
        class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
    />
    <p class="text-xs text-gray-400 mt-1">Accepted: PDF, JPG, PNG. Max size: 5MB</p>
</div>

<!-- Textarea -->
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Remarks</label>
    <textarea
        wire:model="remarks"
        rows="3"
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        placeholder="Enter remarks"
    ></textarea>
</div>
```

### Cards
```html
<!-- Standard card -->
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
    <h3 class="text-base font-semibold text-gray-900 mb-4">Card Title</h3>
    <!-- content -->
</div>

<!-- Stat card for dashboard -->
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
    <p class="text-sm text-gray-500">Total Applications</p>
    <p class="text-3xl font-bold text-gray-900 mt-1">142</p>
    <p class="text-xs text-green-600 mt-1">+12 today</p>
</div>
```

### Tables
```html
<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Reference Code</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Applicant</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Date</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($applications as $application)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs">
                            {{ $application->reference_code }}
                        </td>
                        <td class="px-4 py-3">
                            {{ $application->applicant_last_name }}, {{ $application->applicant_first_name }}
                        </td>
                        <td class="px-4 py-3">
                            <x-application.status-badge :status="$application->status" />
                        </td>
                        <td class="px-4 py-3 text-gray-500">
                            {{ $application->submitted_at->format('M d, Y') }}
                        </td>
                        <td class="px-4 py-3">
                            
                                href="/applications/{{ $application->application_id }}"
                                wire:navigate
                                class="text-blue-600 hover:underline"
                            >
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-gray-400">
                            No applications found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t border-gray-200">
        {{ $applications->links() }}
    </div>
</div>
```

### Status Badge Component
```html
<!-- resources/views/components/application/status-badge.blade.php -->
@props(['status'])

@php
$colors = [
    'submitted'               => 'bg-blue-100 text-blue-800',
    'under_review'            => 'bg-yellow-100 text-yellow-800',
    'forwarded_to_mswd'       => 'bg-yellow-100 text-yellow-800',
    'pending_additional_docs' => 'bg-orange-100 text-orange-800',
    'approved_by_mswd'        => 'bg-teal-100 text-teal-800',
    'coding'                  => 'bg-yellow-100 text-yellow-800',
    'forwarded_to_mayor'      => 'bg-yellow-100 text-yellow-800',
    'approved_by_mayor'       => 'bg-teal-100 text-teal-800',
    'voucher_preparation'     => 'bg-yellow-100 text-yellow-800',
    'forwarded_to_accounting' => 'bg-yellow-100 text-yellow-800',
    'forwarded_to_treasury'   => 'bg-yellow-100 text-yellow-800',
    'cheque_ready'            => 'bg-green-100 text-green-800',
    'claimed'                 => 'bg-gray-100 text-gray-800',
    'on_hold'                 => 'bg-orange-100 text-orange-800',
    'rejected'                => 'bg-red-100 text-red-800',
];
$color = $colors[$status] ?? 'bg-gray-100 text-gray-800';
$label = ucwords(str_replace('_', ' ', $status));
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $color }}">
    {{ $label }}
</span>
```

### Page Layout Pattern
```html
<!-- Standard authenticated page layout -->
<x-layouts.app>
    <!-- Page header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Applications</h1>
            <p class="text-sm text-gray-500 mt-1">Manage and review submitted applications</p>
        </div>
        <div class="flex gap-3">
            <!-- Action buttons -->
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-4">
        <!-- Filter inputs -->
    </div>

    <!-- Content -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
        <!-- Table or list -->
    </div>
</x-layouts.app>
```

### Alert and Feedback Messages
```html
<!-- Success alert -->
<div class="bg-green-50 border border-green-200 rounded-lg p-4 flex items-start gap-3">
    <div class="text-green-500">✓</div>
    <p class="text-sm text-green-800">Application approved successfully.</p>
</div>

<!-- Error alert -->
<div class="bg-red-50 border border-red-200 rounded-lg p-4 flex items-start gap-3">
    <div class="text-red-500">✕</div>
    <p class="text-sm text-red-800">Something went wrong. Please try again.</p>
</div>

<!-- Warning alert -->
<div class="bg-orange-50 border border-orange-200 rounded-lg p-4 flex items-start gap-3">
    <div class="text-orange-500">!</div>
    <p class="text-sm text-orange-800">This application has pending additional documents.</p>
</div>

<!-- Info alert -->
<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 flex items-start gap-3">
    <div class="text-blue-500">i</div>
    <p class="text-sm text-blue-800">Application is currently under review.</p>
</div>
```

---

## Responsive Breakpoints
```
sm:   640px  — small tablets
md:   768px  — tablets
lg:   1024px — small laptops
xl:   1280px — desktops
2xl:  1536px — large screens
```

For AICS — target `lg:` and above as primary since staff use desktop workstations. Mobile support is for the applicant-facing tracking page only.

---

## Rules
- Never write custom CSS for component styles — always use Tailwind utilities
- Never use inline style attributes — always use Tailwind classes
- Always add `transition-colors` to interactive elements (buttons, links)
- Always add `focus:outline-none focus:ring-2` to form inputs for accessibility
- Always use `hover:` variants on clickable elements
- Use `divide-y divide-gray-100` on table rows instead of individual borders
- Use `rounded-xl` for cards, `rounded-lg` for inputs and buttons
- Keep color usage consistent — blue for primary actions, red for destructive, green for success

---

## Change Log
| Date | Change | Updated By |
|---|---|---|
| | Initial Tailwind guide created | |