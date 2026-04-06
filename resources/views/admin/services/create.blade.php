<x-app-layout>
    <x-admin.page-header title="Create Service" subtitle="Add a new service to the platform">
        <x-slot name="breadcrumb">
            <a href="{{ route('admin.services.index') }}" class="hover:text-gray-700">Services</a>
            <span>/</span>
            <span class="text-gray-700">Create</span>
        </x-slot>
    </x-admin.page-header>

    <form method="POST" action="{{ route('admin.services.store') }}">
        @csrf

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6">
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-6 text-lg font-semibold text-gray-900">Basic Information</h2>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="name" class="mb-1 block text-sm font-medium text-gray-700">Name</label>
                            <input id="name" name="name" value="{{ old('name') }}" required>
                        </div>
                        <div>
                            <label for="service_category_id" class="mb-1 block text-sm font-medium text-gray-700">Category</label>
                            <select id="service_category_id" name="service_category_id" required>
                                <option value="">Select category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ (string) old('service_category_id') === (string) $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="slug" class="mb-1 block text-sm font-medium text-gray-700">Slug</label>
                            <input id="slug" name="slug" value="{{ old('slug') }}" required>
                        </div>
                        <div>
                            <label for="icon_name" class="mb-1 block text-sm font-medium text-gray-700">Icon Name</label>
                            <input id="icon_name" name="icon_name" value="{{ old('icon_name') }}" required placeholder="e.g. scissor, brush_2">
                            <p class="mt-1 text-xs text-gray-500">Use Iconsax icon names. <a href="https://pub.dev/packages/iconsax" target="_blank" rel="noreferrer" class="text-primary hover:underline">View reference</a></p>
                        </div>
                        <div>
                            <label for="image_url" class="mb-1 block text-sm font-medium text-gray-700">Image URL</label>
                            <input id="image_url" name="image_url" value="{{ old('image_url') }}" placeholder="https://...">
                        </div>
                        <div class="sm:col-span-2">
                            <label for="description" class="mb-1 block text-sm font-medium text-gray-700">Description</label>
                            <textarea id="description" name="description" rows="3">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-6 text-lg font-semibold text-gray-900">Pricing & Duration</h2>
                    <div class="grid gap-5 sm:grid-cols-3">
                        <div>
                            <label for="currency" class="mb-1 block text-sm font-medium text-gray-700">Currency</label>
                            <input id="currency" name="currency" value="UGX" required>
                        </div>
                        <div>
                            <label for="duration_minutes" class="mb-1 block text-sm font-medium text-gray-700">Duration (min)</label>
                            <input type="number" id="duration_minutes" name="duration_minutes" value="{{ old('duration_minutes', 60) }}" required>
                        </div>
                        <div>
                            <label for="sort_order" class="mb-1 block text-sm font-medium text-gray-700">Sort Order</label>
                            <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}">
                        </div>
                    </div>
                </div>

                @include('admin.services._tier-repeater', [
                    'repeaterId' => 'service-tier-repeater',
                    'tierRows' => old('tiers', [
                        ['name' => 'Saver', 'price_amount' => 0, 'description' => '', 'sort_order' => 1, 'is_active' => true],
                        ['name' => 'Standard', 'price_amount' => 0, 'description' => '', 'sort_order' => 2, 'is_active' => true],
                        ['name' => 'Premium', 'price_amount' => 0, 'description' => '', 'sort_order' => 3, 'is_active' => true],
                    ]),
                ])
            </div>

            <div>
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-500">Settings</h2>
                    <div class="space-y-4">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" checked class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary/20">
                            <span class="text-sm text-gray-700">Active</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="is_featured" value="1" class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary/20">
                            <span class="text-sm text-gray-700">Featured on customer app</span>
                        </label>
                    </div>
                </div>

                <div class="mt-6 flex justify-end lg:justify-start">
                    <button type="submit" class="btn">
                        <x-lucide-plus class="h-4 w-4" />
                        Create Service
                    </button>
                </div>
            </div>
        </div>
    </form>
</x-app-layout>
