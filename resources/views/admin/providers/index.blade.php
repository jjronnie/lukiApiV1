@extends('layouts.admin')

@section('content')
<h1>Provider Applications</h1>
<table><thead><tr><th>Name</th><th>Email</th><th>Status</th><th>Rating</th><th></th></tr></thead><tbody>
@foreach($providers as $provider)
<tr>
    <td>{{ $provider->display_name }}</td>
    <td>{{ $provider->user->email }}</td>
    <td>{{ $provider->verification_status->value ?? $provider->verification_status }}</td>
    <td>{{ $provider->rating_avg }} ({{ $provider->rating_count }})</td>
    <td><a class="btn" href="{{ route('admin.providers.show', $provider) }}">Review</a></td>
</tr>
@endforeach
</tbody></table>
{{ $providers->links() }}
@endsection
