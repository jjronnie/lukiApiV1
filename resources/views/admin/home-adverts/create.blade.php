@extends('layouts.admin')

@section('content')
<div class="actions">
    <h1>Create Home Advert</h1>
</div>
<p>Internal links should use app routes like <code>/services</code>. External links should use full URLs like <code>https://luki.ug</code>.</p>
<form method="POST" action="{{ route('admin.home-adverts.store') }}">
    @csrf
    @include('admin.home-adverts._form', ['advert' => null])
    <button type="submit">Create</button>
</form>
@endsection
