# Alpine.js Reference

## Overview
Alpine.js handles all purely client-side interactions in AICS — modals, dropdowns, toggles, confirmations, tabs, and toast notifications. It requires zero build step and lives directly in Blade templates as HTML attributes.

## Key Directives Reference

| Directive | Purpose |
|---|---|
| `x-data` | Initialize Alpine component with reactive data |
| `x-model` | Two-way binding to form inputs |
| `x-show` | Show or hide element based on condition |
| `x-if` | Conditionally render element (removes from DOM) |
| `x-for` | Loop over array |
| `@click` | Handle click event |
| `@input` | Handle input event |
| `@keydown` | Handle keyboard event |
| `x-text` | Set element text content |
| `x-html` | Set element HTML content |
| `x-bind` | Bind attribute dynamically |
| `:class` | Conditionally apply CSS classes |
| `x-transition` | Add CSS transitions on show/hide |
| `x-trap` | Trap focus inside element when open |
| `$wire` | Access Livewire component from Alpine |
| `$dispatch` | Dispatch custom browser event |
| `$watch` | Watch a property for changes |

---

## Standard Patterns Used in AICS

### Modal
```html
<div x-data="{ open: false }">
    <button @click="open = true">Open Modal</button>
    <div
        x-show="open"
        x-trap="open"
        class="fixed inset-0 z-50 flex items-center justify-center"
    >
        <div
            class="absolute inset-0 bg-black opacity-50"
            @click="open = false"
        ></div>
        <div class="relative bg-white rounded-lg p-6 w-full max-w-md mx-4 z-10">
            <button
                @click="open = false"
                class="absolute top-4 right-4"
            >
                &times;
            </button>
            <!-- Modal content here -->
        </div>
    </div>
</div>
```

### Confirm Before Livewire Action
```html
<div x-data="{ confirm: false }">
    <button
        @click="confirm = true"
        class="bg-green-500 text-white px-4 py-2 rounded"
    >
        Approve
    </button>
    <div x-show="confirm" class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 shadow-xl max-w-sm w-full mx-4">
            <p class="font-medium mb-4">Are you sure you want to approve this application?</p>
            <div class="flex gap-3 justify-end">
                <button
                    @click="confirm = false"
                    class="px-4 py-2 border rounded"
                >
                    Cancel
                </button>
                <button
                    @click="$wire.approve(); confirm = false"
                    class="px-4 py-2 bg-green-500 text-white rounded"
                >
                    Confirm
                </button>
            </div>
        </div>
    </div>
</div>
```

### Tabs
```html
<div x-data="{ activeTab: 'information' }">
    <div class="flex border-b">
        <button
            @click="activeTab = 'information'"
            :class="activeTab === 'information' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500'"
            class="px-4 py-2"
        >
            Information
        </button>
        <button
            @click="activeTab = 'documents'"
            :class="activeTab === 'documents' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500'"
            class="px-4 py-2"
        >
            Documents
        </button>
        <button
            @click="activeTab = 'timeline'"
            :class="activeTab === 'timeline' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500'"
            class="px-4 py-2"
        >
            Timeline
        </button>
    </div>
    <div x-show="activeTab === 'information'"><!-- info content --></div>
    <div x-show="activeTab === 'documents'"><!-- documents content --></div>
    <div x-show="activeTab === 'timeline'"><!-- timeline content --></div>
</div>
```

### Toast Notification
```html
<!-- Place once in your main layout -->
<div
    x-data="{ show: false, message: '', type: 'success' }"
    x-on:toast.window="
        show = true;
        message = $event.detail.message;
        type = $event.detail.type ?? 'success';
        setTimeout(() => show = false, 3000)
    "
>
    <div
        x-show="show"
        x-transition
        :class="type === 'success' ? 'bg-green-500' : 'bg-red-500'"
        class="fixed bottom-4 right-4 text-white px-4 py-3 rounded shadow-lg z-50"
    >
        <span x-text="message"></span>
    </div>
</div>

<!-- Dispatch from Livewire -->
<!-- In PHP: $this->dispatch('toast', message: 'Application approved', type: 'success'); -->
<!-- From Alpine: $dispatch('toast', { message: 'Done', type: 'success' }) -->
```

### Dropdown Menu
```html
<div x-data="{ open: false }" class="relative">
    <button @click="open = !open">Actions</button>
    <div
        x-show="open"
        @click.outside="open = false"
        class="absolute right-0 mt-2 w-48 bg-white border rounded shadow-lg z-10"
    >
        <a href="#" class="block px-4 py-2 hover:bg-gray-50">View</a>
        <a href="#" class="block px-4 py-2 hover:bg-gray-50">Edit</a>
        <button
            @click="$wire.delete(); open = false"
            class="block w-full text-left px-4 py-2 text-red-600 hover:bg-gray-50"
        >
            Delete
        </button>
    </div>
</div>
```

### Accordion
```html
<div x-data="{ expanded: null }">
    <div>
        <button @click="expanded = expanded === 1 ? null : 1">
            Section 1
        </button>
        <div x-show="expanded === 1">Content 1</div>
    </div>
    <div>
        <button @click="expanded = expanded === 2 ? null : 2">
            Section 2
        </button>
        <div x-show="expanded === 2">Content 2</div>
    </div>
</div>
```

### Character Counter for Remarks Input
```html
<div x-data="{ remarks: '', max: 500 }">
    <textarea
        x-model="remarks"
        :maxlength="max"
        rows="3"
        class="w-full border rounded p-2"
    ></textarea>
    <p class="text-sm text-gray-400 text-right">
        <span x-text="remarks.length"></span> / <span x-text="max"></span>
    </p>
</div>
```

### Authorization Letter Conditional Field
```html
<!-- This is the most important dynamic pattern in AICS -->
<div x-data="{ relationship: @entangle('relationship') }">
    <select x-model="relationship" name="relationship">
        <option value="">Select relationship</option>
        <option value="immediate_family">Immediate Family</option>
        <option value="other">Other Relative</option>
    </select>

    <div x-show="relationship === 'other'" x-transition>
        <label>Authorization Letter <span class="text-red-500">*</span></label>
        <input type="file" wire:model="authorizationLetter" accept=".pdf,.jpg,.jpeg,.png" />
        @error('authorizationLetter')
            <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror
    </div>
</div>
```

---

## Rules
- Use Alpine only for interactions that do not need the server
- Never use Alpine to call the database directly
- Use `$wire` to bridge Alpine interactions with Livewire actions
- Use `$dispatch` to send events to the global toast notification handler
- Always use `@click.outside` on dropdowns to close on outside click
- Always use `x-trap` on modals to trap keyboard focus
- Keep Alpine x-data objects simple — complex logic belongs in Livewire