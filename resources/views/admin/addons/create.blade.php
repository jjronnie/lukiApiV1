@extends('layouts.admin')

@section('content')
<h1>Create Add-on</h1>
<form method="POST" action="{{ route('admin.addons.store') }}">
    @csrf
    <label>Service</label>
    <select name="service_id" required>@foreach($services as $service)<option value="{{ $service->id }}">{{ $service->name }}</option>@endforeach</select>
    <label>Name</label><input name="name" required>
    <label>Description</label><textarea name="description"></textarea>
    <label>Price Amount</label><input type="number" name="price_amount" value="0" required>
    <label>Sort Order</label><input type="number" name="sort_order" value="0">
    <label><input type="checkbox" name="is_active" value="1" checked style="width:auto;"> Active</label>
    <button type="submit">Create</button>
</form>
@endsection
