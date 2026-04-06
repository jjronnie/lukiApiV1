<x-app-layout>
    <x-admin.page-header :title="$service->name" subtitle="{{ $service->category?->name ?? 'No category' }}">
        <x-slot name="breadcrumb">
            <a href="{{ route('admin.services.index') }}" class="hover:text-gray-700">Services</a>
            <span>/</span>
            <span class="text-gray-700">{{ $service->name }}</span>
        </x-slot>
        <x-slot name="actions">
            <a class="btn" href="{{ route('admin.services.edit', $service) }}">
                <x-lucide-pencil class="h-4 w-4" />
                Edit
            </a>
            <form method="POST" action="{{ route('admin.services.destroy', $service) }}" onsubmit="return confirm('Delete this service?');">
                @csrf @method('DELETE')
                <button class="btn bg-red-600 hover:bg-red-700 border-red-600" type="submit">
                    <x-lucide-trash-2 class="h-4 w-4" />
                    Delete
                </button>
            </form>
        </x-slot>
    </x-admin.page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            {{-- Tiers --}}
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h2 class="text-base font-semibold text-gray-900">Service Tiers</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-gray-100 bg-gray-50/50">
                            <tr>
                                <th class="px-5 py-3 font-semibold text-gray-600">Name</th>
                                <th class="px-5 py-3 font-semibold text-gray-600">Price</th>
                                <th class="px-5 py-3 font-semibold text-gray-600">Status</th>
                                <th class="px-5 py-3 font-semibold text-gray-600">Sort</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($service->tiers as $tier)
                                <tr class="hover:bg-gray-50/50">
                                    <td class="px-5 py-3 font-medium text-gray-900">{{ $tier->name }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ number_format($tier->price_amount) }} {{ $service->currency }}</td>
                                    <td class="px-5 py-3">
                                        @if ($tier->is_active)
                                            <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700">Active</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-gray-500">{{ $tier->sort_order }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Add-ons --}}
            @if ($service->addOns->count())
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h2 class="text-base font-semibold text-gray-900">Add-ons</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="border-b border-gray-100 bg-gray-50/50">
                                <tr>
                                    <th class="px-5 py-3 font-semibold text-gray-600">Name</th>
                                    <th class="px-5 py-3 font-semibold text-gray-600">Price</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($service->addOns as $addOn)
                                    <tr class="hover:bg-gray-50/50">
                                        <td class="px-5 py-3 font-medium text-gray-900">{{ $addOn->name }}</td>
                                        <td class="px-5 py-3 text-gray-600">{{ number_format($addOn->price_amount) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Pricing Rules --}}
            @if ($service->pricingRules->count())
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h2 class="text-base font-semibold text-gray-900">Pricing Rules</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="border-b border-gray-100 bg-gray-50/50">
                                <tr>
                                    <th class="px-5 py-3 font-semibold text-gray-600">Type</th>
                                    <th class="px-5 py-3 font-semibold text-gray-600">Priority</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($service->pricingRules as $rule)
                                    <tr class="hover:bg-gray-50/50">
                                        <td class="px-5 py-3 text-gray-600">{{ $rule->rule_type->value ?? $rule->rule_type }}</td>
                                        <td class="px-5 py-3 text-gray-600">{{ $rule->priority }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        {{-- Sidebar Details --}}
        <div class="space-y-6">
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-500">Details</h3>
                <dl class="space-y-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Slug</dt>
                        <dd class="mt-0.5 font-mono text-xs text-gray-900">{{ $service->slug }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Icon</dt>
                        <dd class="mt-0.5 text-gray-900">{{ $service->icon_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">From Price</dt>
                        <dd class="mt-0.5 font-medium text-gray-900">{{ number_format($service->base_price_amount) }} {{ $service->currency }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Featured</dt>
                        <dd class="mt-0.5">
                            @if ($service->is_featured)
                                <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700">Yes</span>
                            @else
                                <span class="text-gray-400">No</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Status</dt>
                        <dd class="mt-0.5">
                            @if ($service->is_active)
                                <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700">Active</span>
                            @else
                                <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">Inactive</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            @if ($service->image_url)
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <img src="{{ $service->image_url }}" alt="{{ $service->name }}" class="w-full object-cover">
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
