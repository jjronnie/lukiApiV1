@extends('layouts.admin')

@section('content')
<div class="actions justify-between">
    <h1>Users</h1>
    <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-end gap-2">
        <div>
            <label for="search">Search</label>
            <input id="search" name="search" value="{{ $search }}" placeholder="Name, email, phone, referral">
        </div>
        <div>
            <label for="role">Role</label>
            <select id="role" name="role">
                <option value="">All</option>
                @foreach($availableRoles as $role)
                    <option value="{{ $role }}" {{ $selectedRole === $role ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn" type="submit">Filter</button>
        @if($search !== '' || $selectedRole !== '')
            <a class="btn btn-light" href="{{ route('admin.users.index') }}">Reset</a>
        @endif
    </form>
</div>

<table>
    <thead>
    <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Role</th>
        <th>Provider</th>
        <th>Status</th>
        <th>Joined</th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    @forelse($users as $user)
        @php($userRole = $user->roles->pluck('name')->first() ?? 'user')
        <tr>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->phone ?? '—' }}</td>
            <td>{{ $userRole }}</td>
            <td>{{ $user->providerProfile?->display_name ?? '—' }}</td>
            <td>{{ $user->is_blocked ? 'Blocked' : 'Active' }}</td>
            <td>{{ $user->created_at?->format('d M, Y') }}</td>
            <td class="actions">
                <a class="btn btn-light" href="{{ route('admin.users.edit', $user) }}">Edit</a>
                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete this user?');">
                    @csrf
                    @method('DELETE')
                    <button class="btn" type="submit">Delete</button>
                </form>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="8">No users found.</td>
        </tr>
    @endforelse
    </tbody>
</table>

{{ $users->links() }}
@endsection
