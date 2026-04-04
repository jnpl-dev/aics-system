@if (session('status'))
    <div class="rounded-xl border border-[#6C9C02]/40 bg-[#6C9C02]/10 px-4 py-3 text-sm text-[#176334]">
        {{ session('status') }}
    </div>
@endif

@if ($errors->any())
    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        <p class="font-semibold">Please correct the following:</p>
        <ul class="mt-2 list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
