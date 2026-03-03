<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProviderVerificationStatus;
use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $actor = $this->resolveActor($request);
        $search = trim((string) $request->input('search', ''));
        $selectedRole = (string) $request->input('role', '');
        $availableRoles = $this->availableRoles($actor);

        $users = User::query()
            ->with(['roles', 'providerProfile'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nestedQuery) use ($search) {
                    $nestedQuery
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%')
                        ->orWhere('referral_code', 'like', '%'.$search.'%');
                });
            })
            ->when(
                $selectedRole !== '' && $availableRoles->contains($selectedRole),
                fn ($query) => $query->whereHas('roles', fn ($roleQuery) => $roleQuery->where('name', $selectedRole))
            )
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'search' => $search,
            'selectedRole' => $selectedRole,
            'availableRoles' => $availableRoles->all(),
        ]);
    }

    public function edit(Request $request, User $user): View
    {
        $actor = $this->resolveActor($request);
        $this->abortIfForbiddenManagement($actor, $user);

        return view('admin.users.edit', [
            'managedUser' => $user->load(['roles', 'providerProfile']),
            'availableRoles' => $this->availableRoles($actor)->all(),
            'providerStatuses' => collect(ProviderVerificationStatus::cases())->map(fn (ProviderVerificationStatus $status) => $status->value)->all(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $actor = $this->resolveActor($request);
        $this->abortIfForbiddenManagement($actor, $user);

        $validated = $request->validated();
        $selectedRole = (string) $validated['role'];

        abort_unless($this->availableRoles($actor)->contains($selectedRole), 403);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'referral_code' => $validated['referral_code'] ?? null,
            'is_blocked' => $validated['is_blocked'] ?? false,
        ]);

        $user->syncRoles([$selectedRole]);

        $providerPayload = [
            'provider_type' => $validated['provider_type'] ?? 'individual',
            'display_name' => $validated['provider_display_name'] ?? $user->name,
            'verification_status' => $validated['provider_verification_status'] ?? ProviderVerificationStatus::Pending->value,
        ];

        if ($selectedRole === RoleName::Provider->value) {
            ProviderProfile::query()->updateOrCreate(
                ['user_id' => $user->id],
                $providerPayload
            );
        } elseif ($user->providerProfile !== null) {
            $user->providerProfile->update($providerPayload);
        }

        return redirect()->route('admin.users.index')->with('status', 'User updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $actor = $this->resolveActor($request);
        $this->abortIfForbiddenManagement($actor, $user);

        abort_if($actor->is($user), 422, 'You cannot delete your own account.');

        $user->delete();

        return redirect()->route('admin.users.index')->with('status', 'User deleted.');
    }

    private function resolveActor(Request $request): User
    {
        $actor = $request->user();
        abort_unless($actor instanceof User, 403);

        return $actor;
    }

    private function abortIfForbiddenManagement(User $actor, User $managedUser): void
    {
        if ($actor->hasRole(RoleName::Superadmin->value)) {
            return;
        }

        abort_if(
            $managedUser->hasAnyRole([RoleName::Admin->value, RoleName::Superadmin->value]),
            403
        );
    }

    /**
     * @return Collection<int, string>
     */
    private function availableRoles(User $actor): Collection
    {
        if ($actor->hasRole(RoleName::Superadmin->value)) {
            return collect(RoleName::cases())->map(fn (RoleName $role) => $role->value);
        }

        return collect([
            RoleName::User->value,
            RoleName::Provider->value,
        ]);
    }
}
