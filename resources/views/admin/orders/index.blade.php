<x-app-layout>
    <x-admin.page-header title="Orders" subtitle="Track and manage all platform orders">
    </x-admin.page-header>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-gray-100 bg-gray-50/50">
                    <tr>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Public ID</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Service</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Tier</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Mode</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">User</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Provider</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Status</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Total</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($orders as $order)
                        <tr class="transition-colors hover:bg-gray-50/50">
                            <td class="px-5 py-3.5 font-mono text-xs font-medium text-gray-900">{{ $order->public_id }}</td>
                            <td class="px-5 py-3.5 text-gray-600">{{ $order->service_name_snapshot ?? $order->service?->name ?? '-' }}</td>
                            <td class="px-5 py-3.5 text-gray-600">{{ $order->service_tier_name_snapshot ?? $order->serviceTier?->name ?? 'Legacy' }}</td>
                            <td class="px-5 py-3.5 text-gray-600">{{ $order->booking_mode->value ?? $order->booking_mode }}</td>
                            <td class="px-5 py-3.5 text-gray-600">{{ $order->user->email }}</td>
                            <td class="px-5 py-3.5 text-gray-600">{{ $order->providerProfile?->display_name ?? '-' }}</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700">{{ $order->status->value ?? $order->status }}</span>
                            </td>
                            <td class="px-5 py-3.5 font-medium text-gray-900">{{ number_format($order->total_amount) }}</td>
                            <td class="px-5 py-3.5">
                                <a class="btn btn-light text-xs" href="{{ route('admin.orders.show', $order) }}">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $orders->links() }}
    </div>
</x-app-layout>
