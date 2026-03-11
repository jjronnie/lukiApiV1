@extends('layouts.admin')

@section('content')
<h1>Edit Service Category</h1>
<p>Use Iconsax icon names supported by the mobile apps. Example values: <code>scissor</code>, <code>brush_2</code>, <code>heart</code>, <code>magicpen</code>, <code>category</code>.</p>
<form method="POST" action="{{ route('admin.service-categories.update', $category) }}">
    @csrf
    @method('PUT')
    <label>Name</label><input name="name" value="{{ old('name', $category->name) }}" required>
    <label>Slug</label><input name="slug" value="{{ old('slug', $category->slug) }}" required>
    <label>Icon Name</label><input name="icon_name" value="{{ old('icon_name', $category->icon_name) }}" required>
    <label>Image URL</label><input name="image_url" value="{{ old('image_url', $category->image_url) }}" placeholder="https://...">
    <label>Sort Order</label><input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order) }}">
    <label><input type="checkbox" name="is_active" value="1" {{ $category->is_active ? 'checked' : '' }} style="width:auto;"> Active</label>
    <button type="submit">Save</button>
</form>
@endsection
