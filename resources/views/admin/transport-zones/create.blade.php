@extends('layouts.admin')

@section('content')
<h1>Create Transport Zone</h1>
<p>Use a center point and radius in kilometres. The smallest matching active zone wins. Mark one zone as fallback for locations outside every defined zone.</p>
<form method="POST" action="{{ route('admin.transport-zones.store') }}">
    @csrf
    @include('admin.transport-zones._form', ['zone' => null])
    <button type="submit">Create</button>
</form>
@endsection
