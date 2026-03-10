@extends('layouts.admin')

@section('content')
<h1>Customer Verification Review</h1>
<p>Customer: {{ $verification->user?->name }} ({{ $verification->user?->email }})</p>
<p>Status: {{ $verification->status->value ?? $verification->status }}</p>
<p>ID Type: {{ str_replace('_', ' ', $verification->id_type) }}</p>
@if($verification->rejection_reason)
    <p>Rejection Reason: {{ $verification->rejection_reason }}</p>
@endif

<div class="grid gap-4 md:grid-cols-3">
    <div>
        <p class="mb-2 font-semibold">Selfie</p>
        <a class="btn btn-light" href="{{ route('admin.user-identity-verifications.media', [$verification, 'selfie']) }}" target="_blank">Open Selfie</a>
    </div>
    <div>
        <p class="mb-2 font-semibold">ID Front</p>
        <a class="btn btn-light" href="{{ route('admin.user-identity-verifications.media', [$verification, 'id_front']) }}" target="_blank">Open Front</a>
    </div>
    <div>
        <p class="mb-2 font-semibold">ID Back</p>
        <a class="btn btn-light" href="{{ route('admin.user-identity-verifications.media', [$verification, 'id_back']) }}" target="_blank">Open Back</a>
    </div>
</div>

<h3 class="mt-6">Review Decision</h3>
<form method="POST" action="{{ route('admin.user-identity-verifications.review', $verification) }}">
    @csrf
    <label>Status</label>
    <select name="status" required>
        <option value="approved">Approve</option>
        <option value="rejected">Reject</option>
    </select>
    <label>Rejection Reason</label>
    <textarea name="rejection_reason">{{ old('rejection_reason', $verification->rejection_reason) }}</textarea>
    <button type="submit">Save Decision</button>
</form>
@endsection
