<x-app-layout>
    <x-admin.page-header title="Wallets" subtitle="Monitor provider wallet balances and holds">
    </x-admin.page-header>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-gray-100 bg-gray-50/50">
                    <tr>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Provider</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Balance</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Hold</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Min Required</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Status</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($wallets as $wallet)
                        <tr class="transition-colors hover:bg-gray-50/50">
                            <td class="px-5 py-3.5 font-medium text-gray-900">{{ $wallet->providerProfile->display_name }}</td>
                            <td class="px-5 py-3.5 font-medium text-gray-900">{{ number_format($wallet->balance_amount) }}</td>
                            <td class="px-5 py-3.5 text-gray-600">{{ number_format($wallet->hold_amount) }}</td>
                            <td class="px-5 py-3.5 text-gray-600">{{ number_format($wallet->min_required_amount) }}</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700">{{ $wallet->status->value ?? $wallet->status }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <a class="btn btn-light text-xs" href="{{ route('admin.wallets.show', $wallet) }}">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $wallets->links() }}
    </div>
</x-app-layout>
