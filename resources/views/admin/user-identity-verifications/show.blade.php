@extends('layouts.admin')

@section('content')
<h1>Customer Verification Review</h1>
<div class="card mb-6">
    <p><strong>Customer:</strong> {{ $verification->user?->name }} ({{ $verification->user?->email }})</p>
    <p><strong>Status:</strong> {{ $verification->status->value ?? $verification->status }}</p>
    <p><strong>ID Type:</strong> {{ str_replace('_', ' ', $verification->id_type) }}</p>
    <p><strong>Submitted:</strong> {{ $verification->submitted_at?->format('d M Y H:i') ?? '—' }}</p>
    <p><strong>Reviewed:</strong> {{ $verification->reviewed_at?->format('d M Y H:i') ?? '—' }}</p>
    @if($verification->rejection_reason)
        <p><strong>Rejection Reason:</strong> {{ $verification->rejection_reason }}</p>
    @endif
</div>

<div class="grid gap-4 md:grid-cols-3">
    @foreach (['selfie' => 'Selfie', 'id_front' => 'ID Front', 'id_back' => 'ID Back'] as $collection => $label)
        <div class="card">
            <p class="mb-2 font-semibold">{{ $label }}</p>
            <a href="{{ route('admin.user-identity-verifications.media', [$verification, $collection]) }}" target="_blank">
                <img
                    src="{{ route('admin.user-identity-verifications.media', [$verification, $collection]) }}"
                    alt="{{ $label }}"
                    style="width:100%; height:260px; object-fit:contain; background:#111827; border-radius:14px; padding:12px;"
                >
            </a>
            <div class="actions mt-3">
                <a class="btn btn-light" href="{{ route('admin.user-identity-verifications.media', [$verification, $collection]) }}" target="_blank">Open Full Image</a>
            </div>
        </div>
    @endforeach
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
