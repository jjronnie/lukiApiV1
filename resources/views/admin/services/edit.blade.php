@extends('layouts.admin')

@section('content')
<h1>Edit Service</h1>
<form method="POST" action="{{ route('admin.services.update', $service) }}">
    @csrf
    @method('PUT')
    <label>Slug</label><input name="slug" value="{{ old('slug', $service->slug) }}" required>
    <label>Name</label><input name="name" value="{{ old('name', $service->name) }}" required>
    <label>Description</label><textarea name="description">{{ old('description', $service->description) }}</textarea>
    <label>Currency</label><input name="currency" value="{{ old('currency', $service->currency) }}" required>
    <label>Base Price Amount</label><input type="number" name="base_price_amount" value="{{ old('base_price_amount', $service->base_price_amount) }}" required>
    <label>Duration Minutes</label><input type="number" name="duration_minutes" value="{{ old('duration_minutes', $service->duration_minutes) }}" required>
    <label>Sort Order</label><input type="number" name="sort_order" value="{{ old('sort_order', $service->sort_order) }}">
    <label><input type="checkbox" name="is_active" value="1" {{ $service->is_active ? 'checked' : '' }} style="width:auto;"> Active</label>
    <button type="submit">Save</button>
</form>
@endsection
