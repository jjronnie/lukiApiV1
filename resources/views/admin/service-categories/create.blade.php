<x-app-layout>
    <x-admin.page-header title="Create Service Category" subtitle="Add a new category to organize services">
        <x-slot name="breadcrumb">
            <a href="{{ route('admin.service-categories.index') }}" class="hover:text-gray-700">Categories</a>
            <span>/</span>
            <span class="text-gray-700">Create</span>
        </x-slot>
    </x-admin.page-header>

    <div class="max-w-2xl">
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <form method="POST" action="{{ route('admin.service-categories.store') }}">
                @csrf

                <div class="space-y-5">
                    <div>
                        <label for="name" class="mb-1 block text-sm font-medium text-gray-700">Name</label>
                        <input id="name" name="name" value="{{ old('name') }}" required>
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
                    <div>
                        <label for="sort_order" class="mb-1 block text-sm font-medium text-gray-700">Sort Order</label>
                        <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}">
                    </div>
                    <div class="space-y-3 pt-2">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary/20">
                            <span class="text-sm text-gray-700">Featured on home suggestions</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" checked class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary/20">
                            <span class="text-sm text-gray-700">Active</span>
                        </label>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="btn">
                        <x-lucide-plus class="h-4 w-4" />
                        Create Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
