@extends('layouts.admin')

@section('content')
<h1>{{ $addon->name }}</h1>
<p>Service: {{ $addon->service->name }}</p>
<p>Price: {{ number_format($addon->price_amount) }}</p>
@endsection
