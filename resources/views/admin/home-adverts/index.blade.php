<x-app-layout>
    <x-admin.page-header title="Home Adverts" subtitle="Manage homepage promotional banners and adverts">
        <x-slot name="actions">
            <a class="btn" href="{{ route('admin.home-adverts.create') }}">
                <x-lucide-plus class="h-4 w-4" />
                New Advert
            </a>
        </x-slot>
    </x-admin.page-header>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-gray-100 bg-gray-50/50">
                    <tr>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Title</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Button</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Link</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Status</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Window</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($adverts as $advert)
                        <tr class="transition-colors hover:bg-gray-50/50">
                            <td class="px-5 py-3.5 font-medium text-gray-900">{{ $advert->title }}</td>
                            <td class="px-5 py-3.5 text-gray-600">{{ $advert->button_text ?: '—' }}</td>
                            <td class="px-5 py-3.5 text-gray-500 text-xs">{{ $advert->link_type }}{{ $advert->link_target ? ': ' . $advert->link_target : '' }}</td>
                            <td class="px-5 py-3.5">
                                @if ($advert->is_active)
                                    <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700">Active</span>
                                @else
                                    <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">Inactive</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-gray-500 text-xs">
                                {{ $advert->starts_at?->format('d M Y H:i') ?? 'Now' }} — {{ $advert->ends_at?->format('d M Y H:i') ?? 'Open' }}
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-1.5">
                                    <a class="btn btn-light text-xs" href="{{ route('admin.home-adverts.show', $advert) }}">View</a>
                                    <a class="btn text-xs" href="{{ route('admin.home-adverts.edit', $advert) }}">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $adverts->links() }}
    </div>
</x-app-layout>
