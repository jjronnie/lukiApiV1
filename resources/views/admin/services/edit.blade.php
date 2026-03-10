@extends('layouts.admin')

@section('content')
<h1>Edit Service</h1>
<form method="POST" action="{{ route('admin.services.update', $service) }}">
    @csrf
    @method('PUT')
    <p>Use Iconsax icon names supported by the mobile apps. Example values: <code>scissor</code>, <code>brush_2</code>, <code>heart</code>, <code>magicpen</code>, <code>profile_circle</code>, <code>category</code>.</p>
    <p>Reference: <a href="https://pub.dev/packages/iconsax" target="_blank" rel="noreferrer">Iconsax package icon naming</a>.</p>
    <label>Category</label>
    <select name="service_category_id" required>
        @foreach($categories as $category)
            <option value="{{ $category->id }}" {{ (string) old('service_category_id', $service->service_category_id) === (string) $category->id ? 'selected' : '' }}>
                {{ $category->name }}
            </option>
        @endforeach
    </select>
    <label>Slug</label><input name="slug" value="{{ old('slug', $service->slug) }}" required>
    <label>Name</label><input name="name" value="{{ old('name', $service->name) }}" required>
    <label>Icon Name</label><input name="icon_name" value="{{ old('icon_name', $service->icon_name) }}" required>
    <label>Description</label><textarea name="description">{{ old('description', $service->description) }}</textarea>
    <label>Currency</label><input name="currency" value="{{ old('currency', $service->currency) }}" required>
    <label>Duration Minutes</label><input type="number" name="duration_minutes" value="{{ old('duration_minutes', $service->duration_minutes) }}" required>
    <label>Sort Order</label><input type="number" name="sort_order" value="{{ old('sort_order', $service->sort_order) }}">
    <label><input type="checkbox" name="is_active" value="1" {{ $service->is_active ? 'checked' : '' }} style="width:auto;"> Active</label>
    <label><input type="checkbox" name="is_featured" value="1" {{ $service->is_featured ? 'checked' : '' }} style="width:auto;"> Featured on customer app</label>
    @php
        $existingTiers = $service->tiers->map(fn ($tier) => [
            'id' => $tier->id,
            'name' => $tier->name,
            'price_amount' => $tier->price_amount,
            'description' => $tier->description,
            'sort_order' => $tier->sort_order,
            'is_active' => $tier->is_active,
        ])->all();
    @endphp
    @include('admin.services._tier-repeater', [
        'repeaterId' => 'service-tier-repeater',
        'tierRows' => old('tiers', $existingTiers),
    ])
    <button type="submit">Save</button>
</form>
@endsection
