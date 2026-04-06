<x-app-layout>
    <x-admin.page-header title="Service Add-ons" subtitle="Manage additional services and extras">
        <x-slot name="actions">
            <a class="btn" href="{{ route('admin.addons.create') }}">
                <x-lucide-plus class="h-4 w-4" />
                New Add-on
            </a>
        </x-slot>
    </x-admin.page-header>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-gray-100 bg-gray-50/50">
                    <tr>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Service</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Name</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Price</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($addons as $addon)
                        <tr class="transition-colors hover:bg-gray-50/50">
                            <td class="px-5 py-3.5 text-gray-600">{{ $addon->service->name }}</td>
                            <td class="px-5 py-3.5 font-medium text-gray-900">{{ $addon->name }}</td>
                            <td class="px-5 py-3.5 font-medium text-gray-900">{{ number_format($addon->price_amount) }}</td>
                            <td class="px-5 py-3.5">
                                <a class="btn btn-light text-xs" href="{{ route('admin.addons.edit', $addon) }}">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $addons->links() }}
    </div>
</x-app-layout>
