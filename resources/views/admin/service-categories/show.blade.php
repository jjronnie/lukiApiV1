<x-app-layout>
    <x-admin.page-header :title="$category->name" subtitle="Service category details">
        <x-slot name="breadcrumb">
            <a href="{{ route('admin.service-categories.index') }}" class="hover:text-gray-700">Categories</a>
            <span>/</span>
            <span class="text-gray-700">{{ $category->name }}</span>
        </x-slot>
        <x-slot name="actions">
            <a class="btn" href="{{ route('admin.service-categories.edit', $category) }}">
                <x-lucide-pencil class="h-4 w-4" />
                Edit
            </a>
            <form method="POST" action="{{ route('admin.service-categories.destroy', $category) }}" onsubmit="return confirm('Delete this category?');">
                @csrf @method('DELETE')
                <button class="btn bg-red-600 hover:bg-red-700 border-red-600" type="submit">
                    <x-lucide-trash-2 class="h-4 w-4" />
                    Delete
                </button>
            </form>
        </x-slot>
    </x-admin.page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h2 class="text-base font-semibold text-gray-900">Services in this Category</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-gray-100 bg-gray-50/50">
                            <tr>
                                <th class="px-5 py-3 font-semibold text-gray-600">Name</th>
                                <th class="px-5 py-3 font-semibold text-gray-600">Featured</th>
                                <th class="px-5 py-3 font-semibold text-gray-600">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($category->services as $service)
                                <tr class="hover:bg-gray-50/50">
                                    <td class="px-5 py-3 font-medium text-gray-900">{{ $service->name }}</td>
                                    <td class="px-5 py-3">
                                        @if ($service->is_featured)
                                            <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700">Yes</span>
                                        @else
                                            <span class="text-gray-400">No</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3">
                                        @if ($service->is_active)
                                            <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700">Active</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-5 py-12 text-center text-gray-500">No services in this category.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div>
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-500">Details</h3>
                <dl class="space-y-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Slug</dt>
                        <dd class="mt-0.5 font-mono text-xs text-gray-900">{{ $category->slug }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Icon</dt>
                        <dd class="mt-0.5 text-gray-900">{{ $category->icon_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Image</dt>
                        <dd class="mt-0.5">
                            @if ($category->image_url)
                                <a href="{{ $category->image_url }}" target="_blank" rel="noreferrer" class="text-primary hover:underline text-xs">View image</a>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Featured</dt>
                        <dd class="mt-0.5">
                            @if ($category->is_featured)
                                <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700">Yes</span>
                            @else
                                <span class="text-gray-400">No</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Status</dt>
                        <dd class="mt-0.5">
                            @if ($category->is_active)
                                <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700">Active</span>
                            @else
                                <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">Inactive</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</x-app-layout>
