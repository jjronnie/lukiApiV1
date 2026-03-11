@extends('layouts.admin')

@section('content')
<h1>Create Service Category</h1>
<p>Use Iconsax icon names that the mobile apps support, for example: <code>scissor</code>, <code>brush_2</code>, <code>heart</code>, <code>magicpen</code>, <code>category</code>.</p>
<p>Reference: <a href="https://pub.dev/packages/iconsax" target="_blank" rel="noreferrer">Iconsax package icon naming</a>.</p>
<form method="POST" action="{{ route('admin.service-categories.store') }}">
    @csrf
    <label>Name</label><input name="name" value="{{ old('name') }}" required>
    <label>Slug</label><input name="slug" value="{{ old('slug') }}" required>
    <label>Icon Name</label><input name="icon_name" value="{{ old('icon_name') }}" required>
    <label>Image URL</label><input name="image_url" value="{{ old('image_url') }}" placeholder="https://...">
    <label>Sort Order</label><input type="number" name="sort_order" value="{{ old('sort_order', 0) }}">
    <label><input type="checkbox" name="is_active" value="1" checked style="width:auto;"> Active</label>
    <button type="submit">Create</button>
</form>
@endsection
