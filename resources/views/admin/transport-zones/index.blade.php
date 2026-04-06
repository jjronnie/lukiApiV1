<x-app-layout>
    <x-admin.page-header title="Transport Zones" subtitle="Manage delivery zones and pricing">
        <x-slot name="actions">
            <a class="btn" href="{{ route('admin.transport-zones.create') }}">
                <x-lucide-plus class="h-4 w-4" />
                New Zone
            </a>
        </x-slot>
    </x-admin.page-header>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-gray-100 bg-gray-50/50">
                    <tr>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Name</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Radius</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Fee</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Coordinates</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Fallback</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Status</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($zones as $zone)
                        <tr class="transition-colors hover:bg-gray-50/50">
                            <td class="px-5 py-3.5 font-medium text-gray-900">{{ $zone->name }}</td>
                            <td class="px-5 py-3.5 text-gray-600">{{ $zone->radius_km !== null ? number_format((float) $zone->radius_km, 2).' km' : '—' }}</td>
                            <td class="px-5 py-3.5 text-gray-600">{{ number_format($zone->fee_amount) }} UGX</td>
                            <td class="px-5 py-3.5 font-mono text-xs text-gray-500">{{ $zone->center_lat !== null && $zone->center_lng !== null ? $zone->center_lat . ', ' . $zone->center_lng : '—' }}</td>
                            <td class="px-5 py-3.5">
                                @if ($zone->is_fallback)
                                    <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700">Yes</span>
                                @else
                                    <span class="text-gray-400">No</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                @if ($zone->is_active)
                                    <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700">Active</span>
                                @else
                                    <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">Inactive</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-1.5">
                                    <a class="btn btn-light text-xs" href="{{ route('admin.transport-zones.show', $zone) }}">View</a>
                                    <a class="btn text-xs" href="{{ route('admin.transport-zones.edit', $zone) }}">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $zones->links() }}
    </div>
</x-app-layout>
