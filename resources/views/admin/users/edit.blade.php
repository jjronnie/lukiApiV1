<x-app-layout>
@php($assignedRole = old('role', $managedUser->roles->pluck('name')->first() ?? \App\Enums\RoleName::User->value))
@php($profile = $managedUser->providerProfile)

<h1>Edit User</h1>
<form method="POST" action="{{ route('admin.users.update', $managedUser) }}">
    @csrf
    @method('PUT')

    <label for="name">Name</label>
    <input id="name" name="name" value="{{ old('name', $managedUser->name) }}" required>

    <label for="email">Email</label>
    <input id="email" type="email" name="email" value="{{ old('email', $managedUser->email) }}" required>

    <label for="phone">Phone</label>
    <input id="phone" name="phone" value="{{ old('phone', $managedUser->phone) }}">

    <label for="referral_code">Referral Code</label>
    <input id="referral_code" name="referral_code" value="{{ old('referral_code', $managedUser->referral_code) }}">

    <label for="role">Role</label>
    <select id="role" name="role" required>
        @foreach($availableRoles as $role)
            <option value="{{ $role }}" {{ $assignedRole === $role ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
        @endforeach
    </select>

    <label><input type="checkbox" name="is_blocked" value="1" {{ old('is_blocked', $managedUser->is_blocked) ? 'checked' : '' }} style="width:auto;"> Block user</label>

    <h3 class="mt-6">Provider Details</h3>

    <label for="provider_display_name">Provider Display Name</label>
    <input id="provider_display_name" name="provider_display_name" value="{{ old('provider_display_name', $profile?->display_name ?? $managedUser->name) }}">

    <label for="provider_type">Provider Type</label>
    <select id="provider_type" name="provider_type">
        <option value="individual" {{ old('provider_type', $profile?->provider_type) === 'individual' ? 'selected' : '' }}>Individual</option>
        <option value="business" {{ old('provider_type', $profile?->provider_type) === 'business' ? 'selected' : '' }}>Business</option>
    </select>

    <label for="provider_verification_status">Provider Verification Status</label>
    <select id="provider_verification_status" name="provider_verification_status">
        @foreach($providerStatuses as $status)
            <option value="{{ $status }}" {{ old('provider_verification_status', $profile?->verification_status?->value ?? $profile?->verification_status) === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
        @endforeach
    </select>

    <button type="submit">Save</button>
</form>
</x-app-layout>
