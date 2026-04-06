<x-app-layout>
    <x-admin.page-header title="Edit User" :subtitle="'Managing ' . $managedUser->name">
        <x-slot name="breadcrumb">
            <a href="{{ route('admin.users.index') }}" class="hover:text-gray-700">Users</a>
            <span>/</span>
            <span class="text-gray-700">Edit</span>
        </x-slot>
    </x-admin.page-header>

    @php($assignedRole = old('role', $managedUser->roles->pluck('name')->first() ?? \App\Enums\RoleName::User->value))
    @php($profile = $managedUser->providerProfile)

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="mb-6 text-lg font-semibold text-gray-900">User Details</h2>
                <form method="POST" action="{{ route('admin.users.update', $managedUser) }}">
                    @csrf @method('PUT')

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="name" class="mb-1 block text-sm font-medium text-gray-700">Name</label>
                            <input id="name" name="name" value="{{ old('name', $managedUser->name) }}" required>
                        </div>
                        <div>
                            <label for="email" class="mb-1 block text-sm font-medium text-gray-700">Email</label>
                            <input id="email" type="email" name="email" value="{{ old('email', $managedUser->email) }}" required>
                        </div>
                        <div>
                            <label for="phone" class="mb-1 block text-sm font-medium text-gray-700">Phone</label>
                            <input id="phone" name="phone" value="{{ old('phone', $managedUser->phone) }}">
                        </div>
                        <div>
                            <label for="referral_code" class="mb-1 block text-sm font-medium text-gray-700">Referral Code</label>
                            <input id="referral_code" name="referral_code" value="{{ old('referral_code', $managedUser->referral_code) }}">
                        </div>
                        <div>
                            <label for="role" class="mb-1 block text-sm font-medium text-gray-700">Role</label>
                            <select id="role" name="role" required>
                                @foreach($availableRoles as $role)
                                    <option value="{{ $role }}" {{ $assignedRole === $role ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-end">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_blocked" value="1" {{ old('is_blocked', $managedUser->is_blocked) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500">
                                <span class="text-sm text-gray-700">Block user</span>
                            </label>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="btn">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        <div>
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-lg font-semibold text-gray-900">Provider Details</h2>
                <div class="space-y-4">
                    <div>
                        <label for="provider_display_name" class="mb-1 block text-sm font-medium text-gray-700">Display Name</label>
                        <input id="provider_display_name" name="provider_display_name" form="provider-form" value="{{ old('provider_display_name', $profile?->display_name ?? $managedUser->name) }}">
                    </div>
                    <div>
                        <label for="provider_type" class="mb-1 block text-sm font-medium text-gray-700">Type</label>
                        <select id="provider_type" name="provider_type" form="provider-form">
                            <option value="individual" {{ old('provider_type', $profile?->provider_type) === 'individual' ? 'selected' : '' }}>Individual</option>
                            <option value="business" {{ old('provider_type', $profile?->provider_type) === 'business' ? 'selected' : '' }}>Business</option>
                        </select>
                    </div>
                    <div>
                        <label for="provider_verification_status" class="mb-1 block text-sm font-medium text-gray-700">Verification Status</label>
                        <select id="provider_verification_status" name="provider_verification_status" form="provider-form">
                            @foreach($providerStatuses as $status)
                                <option value="{{ $status }}" {{ old('provider_verification_status', $profile?->verification_status?->value ?? $profile?->verification_status) === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="provider-form" method="POST" action="{{ route('admin.users.update', $managedUser) }}" class="hidden">
        @csrf @method('PUT')
    </form>
</x-app-layout>
