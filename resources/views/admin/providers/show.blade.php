@extends('layouts.admin')

@section('content')
<h1>Provider {{ $provider->display_name }}</h1>
<p>User: {{ $provider->user->name }} ({{ $provider->user->email }})</p>
<p>Status: {{ $provider->verification_status->value ?? $provider->verification_status }}</p>
<p>Services: {{ $provider->services->pluck('name')->join(', ') }}</p>
<p>Wallet Balance: {{ number_format($provider->wallet?->balance_amount ?? 0) }}</p>

<h3>Verification Action</h3>
<form method="POST" action="{{ route('admin.providers.verification.update', $provider) }}">
    @csrf
    <label>Status</label>
    <select name="status" required>
        <option value="approved">Approve</option>
        <option value="rejected">Reject</option>
        <option value="suspended">Suspend</option>
    </select>
    <label>Reason</label>
    <textarea name="reason"></textarea>
    <button type="submit">Submit</button>
</form>

<h3>Documents</h3>
<table><thead><tr><th>Type</th><th>Status</th><th>Reviewed At</th></tr></thead><tbody>
@foreach($provider->documents as $document)
<tr><td>{{ $document->document_type }}</td><td>{{ $document->status }}</td><td>{{ $document->reviewed_at }}</td></tr>
@endforeach
</tbody></table>
@endsection
