<x-app-layout>
    <x-admin.page-header title="Providers" subtitle="Review and manage provider applications">
        <x-slot name="actions">
            <form method="GET" action="{{ route('admin.providers.index') }}" class="flex flex-wrap items-end gap-2">
                <div>
                    <label for="search" class="mb-1 block text-xs font-medium text-gray-500">Search</label>
                    <input id="search" name="search" value="{{ $search ?? '' }}" placeholder="Name, email, or provider number" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-primary/50 focus:ring-2 focus:ring-primary/20">
                </div>
                <div>
                    <label for="status" class="mb-1 block text-xs font-medium text-gray-500">Status</label>
                    <select id="status" name="status" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 focus:border-primary/50 focus:ring-2 focus:ring-primary/20">
                        <option value="">All</option>
                        <option value="pending" {{ ($statusFilter ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ ($statusFilter ?? '') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ ($statusFilter ?? '') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="suspended" {{ ($statusFilter ?? '') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>
                <button class="btn" type="submit">Filter</button>
            </form>
        </x-slot>
    </x-admin.page-header>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-gray-100 bg-gray-50/50">
                    <tr>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Name</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Provider Number</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Email</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Status</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Rating</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($providers as $provider)
                        <tr class="transition-colors hover:bg-gray-50/50">
                            <td class="px-5 py-3.5 font-medium text-gray-900">{{ $provider->display_name }}</td>
                            <td class="px-5 py-3.5 font-mono text-xs text-gray-600">{{ $provider->provider_number ?? '—' }}</td>
                            <td class="px-5 py-3.5 text-gray-600">{{ $provider->user->email }}</td>
                            <td class="px-5 py-3.5">
                                @php($statusColor = match($provider->verification_status->value ?? '') { 'pending' => 'bg-amber-50 text-amber-700', 'approved' => 'bg-emerald-50 text-emerald-700', 'rejected' => 'bg-red-50 text-red-700', 'suspended' => 'bg-gray-100 text-gray-700', default => 'bg-gray-100 text-gray-700' })
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusColor }}">{{ $provider->verification_status->value ?? $provider->verification_status }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-gray-600">{{ $provider->rating_avg }} ({{ $provider->rating_count }})</td>
                            <td class="px-5 py-3.5">
                                <a class="btn text-xs" href="{{ route('admin.providers.show', $provider) }}">Review</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $providers->links() }}
    </div>
</x-app-layout>
