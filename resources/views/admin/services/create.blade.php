<x-app-layout>
<h1>Create Service</h1>
<form method="POST" action="{{ route('admin.services.store') }}">
    @csrf
    <p>Use Iconsax icon names supported by the mobile apps. Example values: <code>scissor</code>, <code>brush_2</code>, <code>heart</code>, <code>magicpen</code>, <code>profile_circle</code>, <code>category</code>.</p>
    <p>Reference: <a href="https://pub.dev/packages/iconsax" target="_blank" rel="noreferrer">Iconsax package icon naming</a>.</p>
    <label>Category</label>
    <select name="service_category_id" required>
        <option value="">Select category</option>
        @foreach($categories as $category)
            <option value="{{ $category->id }}" {{ (string) old('service_category_id') === (string) $category->id ? 'selected' : '' }}>
                {{ $category->name }}
            </option>
        @endforeach
    </select>
    <label>Slug</label><input name="slug" value="{{ old('slug') }}" required>
    <label>Name</label><input name="name" value="{{ old('name') }}" required>
    <label>Icon Name</label><input name="icon_name" value="{{ old('icon_name') }}" required>
    <label>Image URL</label><input name="image_url" value="{{ old('image_url') }}" placeholder="https://...">
    <label>Description</label><textarea name="description">{{ old('description') }}</textarea>
    <label>Currency</label><input name="currency" value="UGX" required>
    <label>Duration Minutes</label><input type="number" name="duration_minutes" value="{{ old('duration_minutes', 60) }}" required>
    <label>Sort Order</label><input type="number" name="sort_order" value="{{ old('sort_order', 0) }}">
    <label><input type="checkbox" name="is_active" value="1" checked style="width:auto;"> Active</label>
    <label><input type="checkbox" name="is_featured" value="1" style="width:auto;"> Featured on customer app</label>
    @include('admin.services._tier-repeater', [
        'repeaterId' => 'service-tier-repeater',
        'tierRows' => old('tiers', [
            ['name' => 'Saver', 'price_amount' => 0, 'description' => '', 'sort_order' => 1, 'is_active' => true],
            ['name' => 'Standard', 'price_amount' => 0, 'description' => '', 'sort_order' => 2, 'is_active' => true],
            ['name' => 'Premium', 'price_amount' => 0, 'description' => '', 'sort_order' => 3, 'is_active' => true],
        ]),
    ])
    <button type="submit">Create</button>
</form>
</x-app-layout>
