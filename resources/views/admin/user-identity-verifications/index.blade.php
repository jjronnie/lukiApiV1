<x-app-layout>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[#8b7d72]">Customer Verification</p>
                <h1 class="mt-1 text-2xl font-display">Verification queue</h1>
                <p class="mt-2 max-w-2xl text-sm text-[#6b7280]">
                    Review submitted customer identity checks, capture the verified identity details, and approve or reject each record.
                </p>
            </div>

            <form
                method="GET"
                action="{{ route('admin.user-identity-verifications.index') }}"
                class="grid gap-3 rounded-2xl border border-[#f1e7dd] bg-[#fffdf9] p-4 sm:grid-cols-2 xl:min-w-[520px]"
            >
                <div class="sm:col-span-2">
                    <label for="search">Search</label>
                    <input
                        id="search"
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search by customer, email, ID type, or ID number"
                    >
                </div>
                <div>
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">All statuses</option>
                        @foreach(['pending', 'approved', 'rejected'] as $status)
                            <option value="{{ $status }}" {{ $statusFilter === $status ? 'selected' : '' }}>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-wrap items-end gap-2">
                    <button class="btn flex-1" type="submit">Apply filters</button>
                    @if($statusFilter !== '' || $search !== '')
                        <a class="btn btn-light flex-1" href="{{ route('admin.user-identity-verifications.index') }}">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="admin-card">
                <p class="admin-card-label">Total records</p>
                <p class="admin-card-value">{{ number_format($statusCounts['all'] ?? 0) }}</p>
                <p class="admin-card-meta">All customer verification submissions in the system.</p>
            </article>
            <article class="admin-card">
                <p class="admin-card-label">Pending</p>
                <p class="admin-card-value">{{ number_format($statusCounts['pending'] ?? 0) }}</p>
                <p class="admin-card-meta">Awaiting a review decision.</p>
            </article>
            <article class="admin-card">
                <p class="admin-card-label">Approved</p>
                <p class="admin-card-value">{{ number_format($statusCounts['approved'] ?? 0) }}</p>
                <p class="admin-card-meta">Completed reviews with verified details saved.</p>
            </article>
            <article class="admin-card">
                <p class="admin-card-label">Rejected</p>
                <p class="admin-card-value">{{ number_format($statusCounts['rejected'] ?? 0) }}</p>
                <p class="admin-card-meta">Need a new submission from the customer.</p>
            </article>
        </section>

        <section class="admin-card space-y-4">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="admin-card-label">Queue</p>
                    <h2 class="mt-1 text-xl font-semibold text-[#1f2937]">Submitted records</h2>
                </div>
                <p class="text-sm text-[#6b7280]">{{ $verifications->total() }} result{{ $verifications->total() === 1 ? '' : 's' }}</p>
            </div>

            <div class="space-y-3 lg:hidden">
                @forelse($verifications as $verification)
                    @php
                        $status = $verification->status->value ?? $verification->status;
                        $statusClasses = match ($status) {
                            'approved' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                            'rejected' => 'border-rose-200 bg-rose-50 text-rose-700',
                            default => 'border-amber-200 bg-amber-50 text-amber-700',
                        };
                    @endphp

                    <article class="rounded-2xl border border-[#f1e7dd] bg-[#fffdf9] p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="truncate text-base font-semibold text-[#1f2937]">
                                    {{ $verification->user?->name ?? 'Unknown customer' }}
                                </h3>
                                <p class="truncate text-sm text-[#6b7280]">{{ $verification->user?->email ?? 'No email' }}</p>
                            </div>
                            <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold capitalize {{ $statusClasses }}">
                                {{ str_replace('_', ' ', $status) }}
                            </span>
                        </div>

                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.1em] text-[#8b7d72]">ID type</p>
                                <p class="mt-1 text-sm capitalize text-[#1f2937]">{{ str_replace('_', ' ', $verification->id_type) }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.1em] text-[#8b7d72]">ID number</p>
                                <p class="mt-1 text-sm text-[#1f2937]">{{ $verification->id_number ?: '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.1em] text-[#8b7d72]">Submitted</p>
                                <p class="mt-1 text-sm text-[#1f2937]">{{ $verification->submitted_at?->format('d M Y H:i') ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.1em] text-[#8b7d72]">Reviewed</p>
                                <p class="mt-1 text-sm text-[#1f2937]">{{ $verification->reviewed_at?->format('d M Y H:i') ?? '—' }}</p>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <a class="btn" href="{{ route('admin.user-identity-verifications.show', $verification) }}">Review record</a>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-[#d8cec2] bg-[#fffdf9] px-4 py-12 text-center text-sm text-[#8b7d72]">
                        No verification records match the current filters.
                    </div>
                @endforelse
            </div>

            <div class="hidden overflow-x-auto lg:block">
                <table>
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>ID Type</th>
                            <th>ID Number</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Reviewed</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($verifications as $verification)
                            @php
                                $status = $verification->status->value ?? $verification->status;
                                $statusClasses = match ($status) {
                                    'approved' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                    'rejected' => 'border-rose-200 bg-rose-50 text-rose-700',
                                    default => 'border-amber-200 bg-amber-50 text-amber-700',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <div class="font-medium text-[#1f2937]">{{ $verification->user?->name ?? 'Unknown customer' }}</div>
                                    <div class="text-xs text-[#6b7280]">{{ $verification->user?->email ?? 'No email' }}</div>
                                </td>
                                <td class="capitalize">{{ str_replace('_', ' ', $verification->id_type) }}</td>
                                <td>{{ $verification->id_number ?: '—' }}</td>
                                <td>
                                    <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold capitalize {{ $statusClasses }}">
                                        {{ str_replace('_', ' ', $status) }}
                                    </span>
                                </td>
                                <td>{{ $verification->submitted_at?->format('d M Y H:i') ?? '—' }}</td>
                                <td>{{ $verification->reviewed_at?->format('d M Y H:i') ?? '—' }}</td>
                                <td class="actions">
                                    <a class="btn" href="{{ route('admin.user-identity-verifications.show', $verification) }}">Review</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">No verification records match the current filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                {{ $verifications->links() }}
            </div>
        </section>
    </div>
</x-app-layout>
