@extends('layouts.admin')

@section('content')
<h1>Create Service</h1>
<form method="POST" action="{{ route('admin.services.store') }}">
    @csrf
    <label>Slug</label><input name="slug" value="{{ old('slug') }}" required>
    <label>Name</label><input name="name" value="{{ old('name') }}" required>
    <label>Description</label><textarea name="description">{{ old('description') }}</textarea>
    <label>Currency</label><input name="currency" value="UGX" required>
    <label>Base Price Amount</label><input type="number" name="base_price_amount" value="{{ old('base_price_amount', 0) }}" required>
    <label>Duration Minutes</label><input type="number" name="duration_minutes" value="{{ old('duration_minutes', 60) }}" required>
    <label>Sort Order</label><input type="number" name="sort_order" value="{{ old('sort_order', 0) }}">
    <label><input type="checkbox" name="is_active" value="1" checked style="width:auto;"> Active</label>
    <button type="submit">Create</button>
</form>
@endsection
