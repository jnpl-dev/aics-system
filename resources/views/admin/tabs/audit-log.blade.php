@php
    $auditPaginator = $auditLogs ?? null;
    $isPaginator = $auditPaginator instanceof \Illuminate\Contracts\Pagination\Paginator;
    $rows = $isPaginator ? $auditPaginator->items() : (is_iterable($auditPaginator) ? $auditPaginator : []);
    $basePaginationUrl = route('dashboard.content', ['tab' => 'audit-log']);
@endphp

<div class="relative flex w-full flex-col rounded-xl border border-[#1F6336]/15 bg-white bg-clip-border text-gray-700 shadow-md">
    <div class="relative mx-4 mt-4 overflow-hidden rounded-none bg-white bg-clip-border text-gray-700">
    <div class="mb-4 flex items-center justify-between gap-8">
            <div>
                <h5 class="block text-xl font-semibold leading-snug tracking-normal text-[#1F6336]">Audit logs</h5>
                <p class="mt-1 block text-sm font-normal leading-relaxed text-gray-600">
                    Authentication and OTP events (paginated at 20 rows per page).
                </p>
            </div>
            <div class="flex shrink-0 flex-col gap-2 sm:flex-row">
                <span class="rounded-lg border border-[#1F6336]/20 px-4 py-2 text-center text-xs font-bold uppercase text-[#1F6336]">
                    Total {{ $isPaginator ? $auditPaginator->total() : count($rows) }}
                </span>
                <a
                    href="{{ $basePaginationUrl }}?audit_page=1"
                    data-audit-pagination
                    class="flex items-center gap-2 rounded-lg bg-[#1F6336] px-4 py-2 text-center text-xs font-bold uppercase text-white shadow-md shadow-[#1F6336]/20 transition-all hover:shadow-lg hover:shadow-[#1F6336]/30"
                >
                    View latest
                </a>
            </div>
        </div>
    </div>

    <div class="max-h-[24rem] overflow-auto px-6 pb-0">
        <table class="mt-2 w-full min-w-max table-auto text-left">
            <thead class="sticky top-0 z-10">
                <tr>
                    <th class="border-y border-[#1F6336]/10 bg-[#F0F3EF] p-4">
                        <p class="text-xs font-semibold uppercase leading-none text-gray-600">Time</p>
                    </th>
                    <th class="border-y border-[#1F6336]/10 bg-[#F0F3EF] p-4">
                        <p class="text-xs font-semibold uppercase leading-none text-gray-600">Module</p>
                    </th>
                    <th class="border-y border-[#1F6336]/10 bg-[#F0F3EF] p-4">
                        <p class="text-xs font-semibold uppercase leading-none text-gray-600">Action</p>
                    </th>
                    <th class="border-y border-[#1F6336]/10 bg-[#F0F3EF] p-4">
                        <p class="text-xs font-semibold uppercase leading-none text-gray-600">Event</p>
                    </th>
                    <th class="border-y border-[#1F6336]/10 bg-[#F0F3EF] p-4">
                        <p class="text-xs font-semibold uppercase leading-none text-gray-600">User</p>
                    </th>
                    <th class="border-y border-[#1F6336]/10 bg-[#F0F3EF] p-4">
                        <p class="text-xs font-semibold uppercase leading-none text-gray-600">IP Address</p>
                    </th>
                    <th class="border-y border-[#1F6336]/10 bg-[#F0F3EF] p-4">
                        <p class="text-xs font-semibold uppercase leading-none text-gray-600">Description</p>
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $log)
                    @php
                        $eventCode = null;
                        if (is_string($log->description) && str_contains($log->description, 'event=')) {
                            $afterEvent = explode('event=', $log->description, 2)[1] ?? '';
                            $eventCode = trim(explode(';', $afterEvent, 2)[0]);
                        }
                    @endphp
                    <tr>
                        <td class="border-b border-[#1F6336]/10 p-4">
                            <p class="text-sm text-gray-700">{{ optional($log->timestamp)->format('Y-m-d H:i:s') ?? '-' }}</p>
                        </td>
                        <td class="border-b border-[#1F6336]/10 p-4">
                            <p class="text-sm font-medium text-[#1F6336]">{{ $log->module }}</p>
                        </td>
                        <td class="border-b border-[#1F6336]/10 p-4">
                            <p class="text-sm text-gray-700">{{ $log->action }}</p>
                        </td>
                        <td class="border-b border-[#1F6336]/10 p-4">
                            <div class="w-max">
                                <div class="rounded-md bg-[#1F6336]/10 px-2 py-1 text-xs font-bold uppercase text-[#1F6336]">
                                    {{ $eventCode ?? '-' }}
                                </div>
                            </div>
                        </td>
                        <td class="border-b border-[#1F6336]/10 p-4">
                            <p class="text-sm text-gray-700">#{{ $log->user_id }}</p>
                        </td>
                        <td class="border-b border-[#1F6336]/10 p-4">
                            <p class="text-sm text-gray-700">{{ $log->ip_address ?? '-' }}</p>
                        </td>
                        <td class="border-b border-[#1F6336]/10 p-4">
                            <p class="text-sm text-gray-600">{{ $log->description ?? '-' }}</p>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-6 text-center text-sm text-gray-500">No audit records yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($isPaginator)
        <div class="flex items-center justify-between border-t border-[#1F6336]/10 p-4">
            <p class="text-sm font-normal text-gray-700">
                Page {{ $auditPaginator->currentPage() }} of {{ $auditPaginator->lastPage() }}
            </p>
            <div class="flex gap-2">
                @if ($auditPaginator->onFirstPage())
                    <span class="rounded-lg border border-gray-300 px-4 py-2 text-xs font-bold uppercase text-gray-400">Previous</span>
                @else
                    <a
                        href="{{ $basePaginationUrl }}?audit_page={{ $auditPaginator->currentPage() - 1 }}"
                        data-audit-pagination
                        class="rounded-lg border border-[#1F6336]/30 px-4 py-2 text-xs font-bold uppercase text-[#1F6336] transition-all hover:bg-[#1F6336]/10"
                    >
                        Previous
                    </a>
                @endif

                @if ($auditPaginator->hasMorePages())
                    <a
                        href="{{ $basePaginationUrl }}?audit_page={{ $auditPaginator->currentPage() + 1 }}"
                        data-audit-pagination
                        class="rounded-lg border border-[#1F6336]/30 px-4 py-2 text-xs font-bold uppercase text-[#1F6336] transition-all hover:bg-[#1F6336]/10"
                    >
                        Next
                    </a>
                @else
                    <span class="rounded-lg border border-gray-300 px-4 py-2 text-xs font-bold uppercase text-gray-400">Next</span>
                @endif
            </div>
        </div>
    @endif
</div>
