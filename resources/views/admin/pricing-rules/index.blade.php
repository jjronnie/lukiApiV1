<x-app-layout>
    <x-admin.page-header title="Pricing Rules" subtitle="Configure pricing tiers and rules for services">
        <x-slot name="actions">
            <a class="btn" href="{{ route('admin.pricing-rules.create') }}">
                <x-lucide-plus class="h-4 w-4" />
                New Rule
            </a>
        </x-slot>
    </x-admin.page-header>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-gray-100 bg-gray-50/50">
                    <tr>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Service</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Type</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Priority</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Status</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($rules as $rule)
                        <tr class="transition-colors hover:bg-gray-50/50">
                            <td class="px-5 py-3.5 font-medium text-gray-900">{{ $rule->service?->name ?? 'Global' }}</td>
                            <td class="px-5 py-3.5 text-gray-600">{{ $rule->rule_type->value ?? $rule->rule_type }}</td>
                            <td class="px-5 py-3.5 text-gray-600">{{ $rule->priority }}</td>
                            <td class="px-5 py-3.5">
                                @if ($rule->is_active)
                                    <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700">Active</span>
                                @else
                                    <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">Inactive</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <a class="btn btn-light text-xs" href="{{ route('admin.pricing-rules.edit', $rule) }}">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $rules->links() }}
    </div>
</x-app-layout>
