@extends('layouts.admin')

@section('content')
<h1>Edit Add-on</h1>
<form method="POST" action="{{ route('admin.addons.update', $addon) }}">
    @csrf
    @method('PUT')
    <label>Service</label>
    <select name="service_id" required>@foreach($services as $service)<option value="{{ $service->id }}" {{ $addon->service_id === $service->id ? 'selected' : '' }}>{{ $service->name }}</option>@endforeach</select>
    <label>Name</label><input name="name" value="{{ $addon->name }}" required>
    <label>Description</label><textarea name="description">{{ $addon->description }}</textarea>
    <label>Price Amount</label><input type="number" name="price_amount" value="{{ $addon->price_amount }}" required>
    <label>Sort Order</label><input type="number" name="sort_order" value="{{ $addon->sort_order }}">
    <label><input type="checkbox" name="is_active" value="1" {{ $addon->is_active ? 'checked' : '' }} style="width:auto;"> Active</label>
    <button type="submit">Save</button>
</form>
@endsection
