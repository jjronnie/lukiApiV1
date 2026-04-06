<x-app-layout>
    <x-admin.page-header title="Users" subtitle="Manage all registered users and their roles">
        <x-slot name="actions">
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-end gap-2">
                <div>
                    <label for="search" class="mb-1 block text-xs font-medium text-gray-500">Search</label>
                    <input id="search" name="search" value="{{ $search }}" placeholder="Name, email, phone" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-primary/50 focus:ring-2 focus:ring-primary/20">
                </div>
                <div>
                    <label for="role" class="mb-1 block text-xs font-medium text-gray-500">Role</label>
                    <select id="role" name="role" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 focus:border-primary/50 focus:ring-2 focus:ring-primary/20">
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
        </x-slot>
    </x-admin.page-header>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-gray-100 bg-gray-50/50">
                    <tr>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Name</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Email</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Phone</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Role</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Provider</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Status</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600">Joined</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-600"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $user)
                        @php($userRole = $user->roles->pluck('name')->first() ?? 'user')
                        <tr class="transition-colors hover:bg-gray-50/50">
                            <td class="px-5 py-3.5 font-medium text-gray-900">{{ $user->name }}</td>
                            <td class="px-5 py-3.5 text-gray-600">{{ $user->email }}</td>
                            <td class="px-5 py-3.5 text-gray-600">{{ $user->phone ?? '—' }}</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">{{ $userRole }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-gray-600">{{ $user->providerProfile?->display_name ?? '—' }}</td>
                            <td class="px-5 py-3.5">
                                @if ($user->is_blocked)
                                    <span class="inline-flex rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-700">Blocked</span>
                                @else
                                    <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700">Active</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-gray-500">{{ $user->created_at?->format('d M, Y') }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-1.5">
                                    <a class="btn btn-light text-xs" href="{{ route('admin.users.edit', $user) }}">Edit</a>
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete this user?');">
                                        @csrf @method('DELETE')
                                        <button class="btn text-xs bg-red-600 hover:bg-red-700 border-red-600" type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-gray-500">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
</x-app-layout>
