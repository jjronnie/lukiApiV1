@extends('layouts.admin')

@section('content')
<h1>Edit Transport Zone</h1>
<form method="POST" action="{{ route('admin.transport-zones.update', $zone) }}">
    @csrf
    @method('PUT')
    @include('admin.transport-zones._form', ['zone' => $zone])
    <button type="submit">Save</button>
</form>
@endsection
