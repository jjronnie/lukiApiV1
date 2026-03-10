@extends('layouts.admin')

@section('content')
<div class="actions justify-between">
    <h1>Customer Verification</h1>
    <form method="GET" action="{{ route('admin.user-identity-verifications.index') }}" class="flex items-end gap-2">
        <div>
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="">All</option>
                @foreach(['pending', 'approved', 'rejected'] as $status)
                    <option value="{{ $status }}" {{ $statusFilter === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn" type="submit">Filter</button>
    </form>
</div>
<table>
    <thead><tr><th>Customer</th><th>ID Type</th><th>Status</th><th>Submitted</th><th>Reviewed</th><th></th></tr></thead>
    <tbody>
    @foreach($verifications as $verification)
        <tr>
            <td>{{ $verification->user?->name }}<br>{{ $verification->user?->email }}</td>
            <td>{{ str_replace('_', ' ', $verification->id_type) }}</td>
            <td>{{ $verification->status->value ?? $verification->status }}</td>
            <td>{{ $verification->submitted_at?->format('d M Y H:i') ?? '—' }}</td>
            <td>{{ $verification->reviewed_at?->format('d M Y H:i') ?? '—' }}</td>
            <td class="actions">
                <a class="btn" href="{{ route('admin.user-identity-verifications.show', $verification) }}">Review</a>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
{{ $verifications->links() }}
@endsection
