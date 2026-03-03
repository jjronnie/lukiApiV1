<?php

use App\Enums\RoleName;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

it('lists users on admin users page', function () {
    $this->seed([RolesAndPermissionsSeeder::class]);

    $admin = User::factory()->create();
    $admin->assignRole(RoleName::Admin->value);

    $customer = User::factory()->create([
        'name' => 'Customer One',
        'email' => 'customer.one@example.com',
    ]);
    $customer->assignRole(RoleName::User->value);

    $response = $this->actingAs($admin)->get(route('admin.users.index'));

    $response
        ->assertSuccessful()
        ->assertSee('Users')
        ->assertSee('customer.one@example.com');
});

it('updates user details and role from admin users page', function () {
    $this->seed([RolesAndPermissionsSeeder::class]);

    $admin = User::factory()->create();
    $admin->assignRole(RoleName::Admin->value);

    $customer = User::factory()->create([
        'name' => 'Customer One',
        'email' => 'customer.update@example.com',
    ]);
    $customer->assignRole(RoleName::User->value);

    $response = $this->actingAs($admin)->put(route('admin.users.update', $customer), [
        'name' => 'Updated Provider User',
        'email' => 'updated.provider@example.com',
        'phone' => '+256700000111',
        'referral_code' => 'REF-001',
        'is_blocked' => true,
        'role' => RoleName::Provider->value,
        'provider_display_name' => 'Updated Provider',
        'provider_type' => 'business',
        'provider_verification_status' => 'approved',
    ]);

    $response->assertRedirect(route('admin.users.index'));

    expect($customer->fresh()?->hasRole(RoleName::Provider->value))->toBeTrue();

    $this->assertDatabaseHas('users', [
        'id' => $customer->id,
        'name' => 'Updated Provider User',
        'email' => 'updated.provider@example.com',
        'phone' => '+256700000111',
        'referral_code' => 'REF-001',
        'is_blocked' => true,
    ]);

    $this->assertDatabaseHas('provider_profiles', [
        'user_id' => $customer->id,
        'display_name' => 'Updated Provider',
        'provider_type' => 'business',
        'verification_status' => 'approved',
    ]);
});

it('deletes a user from admin users page', function () {
    $this->seed([RolesAndPermissionsSeeder::class]);

    $admin = User::factory()->create();
    $admin->assignRole(RoleName::Admin->value);

    $customer = User::factory()->create([
        'email' => 'customer.delete@example.com',
    ]);
    $customer->assignRole(RoleName::User->value);

    $response = $this->actingAs($admin)->delete(route('admin.users.destroy', $customer));

    $response->assertRedirect(route('admin.users.index'));

    $this->assertDatabaseMissing('users', [
        'id' => $customer->id,
    ]);
});
