<x-app-layout>
    <x-admin.page-header title="Services" subtitle="Manage all available services and their configurations">
        <x-slot name="actions">
            <a class="btn" href="{{ route('admin.services.create') }}">
                <x-lucide-plus class="h-4 w-4" />
                New Service
            </a>
        </x-slot>
    </x-admin.page-header>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-gray-100 bg-gray-50/50">
                    <tr>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Name</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Category</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Icon</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Slug</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">From Price</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Active Tiers</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Featured</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Status</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($services as $service)
                        <tr class="transition-colors hover:bg-gray-50/50">
                            <td class="px-5 py-3.5 font-medium text-gray-900">{{ $service->name }}</td>
                            <td class="px-5 py-3.5 text-gray-600">{{ $service->category?->name ?? '—' }}</td>
                            <td class="px-5 py-3.5 text-gray-500">{{ $service->icon_name }}</td>
                            <td class="px-5 py-3.5 text-gray-500 font-mono text-xs">{{ $service->slug }}</td>
                            <td class="px-5 py-3.5 text-gray-900 font-medium">{{ number_format($service->base_price_amount) }} {{ $service->currency }}</td>
                            <td class="px-5 py-3.5 text-gray-600">{{ $service->tiers_count }}</td>
                            <td class="px-5 py-3.5">
                                @if ($service->is_featured)
                                    <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700">Yes</span>
                                @else
                                    <span class="text-gray-400">No</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                @if ($service->is_active)
                                    <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700">Active</span>
                                @else
                                    <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">Inactive</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-1.5">
                                    <a class="btn btn-light text-xs" href="{{ route('admin.services.show', $service) }}">View</a>
                                    <a class="btn text-xs" href="{{ route('admin.services.edit', $service) }}">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $services->links() }}
    </div>
</x-app-layout>
