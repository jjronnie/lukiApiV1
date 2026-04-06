<x-app-layout>
<h1>Provider Applications</h1>
<form method="GET" style="display:grid; gap:12px; grid-template-columns: 1.5fr 1fr auto; margin-bottom: 16px;">
    <input name="search" value="{{ $search ?? '' }}" placeholder="Search name, email, or provider number">
    <select name="status">
        <option value="">All statuses</option>
        <option value="pending" {{ ($statusFilter ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
        <option value="approved" {{ ($statusFilter ?? '') === 'approved' ? 'selected' : '' }}>Approved</option>
        <option value="rejected" {{ ($statusFilter ?? '') === 'rejected' ? 'selected' : '' }}>Rejected</option>
        <option value="suspended" {{ ($statusFilter ?? '') === 'suspended' ? 'selected' : '' }}>Suspended</option>
    </select>
    <button type="submit">Filter</button>
</form>
<table><thead><tr><th>Name</th><th>Provider Number</th><th>Email</th><th>Status</th><th>Rating</th><th></th></tr></thead><tbody>
@foreach($providers as $provider)
<tr>
    <td>{{ $provider->display_name }}</td>
    <td>{{ $provider->provider_number ?? '—' }}</td>
    <td>{{ $provider->user->email }}</td>
    <td>{{ $provider->verification_status->value ?? $provider->verification_status }}</td>
    <td>{{ $provider->rating_avg }} ({{ $provider->rating_count }})</td>
    <td><a class="btn" href="{{ route('admin.providers.show', $provider) }}">Review</a></td>
</tr>
@endforeach
</tbody></table>
{{ $providers->links() }}
</x-app-layout>
