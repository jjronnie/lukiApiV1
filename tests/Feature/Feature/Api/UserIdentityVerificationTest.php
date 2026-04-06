<?php

use App\Enums\RoleName;
use App\Enums\UserIdentityVerificationStatus;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('submits customer identity verification and prevents duplicate pending submissions', function () {
    Storage::fake('local');
    $this->seed(RolesAndPermissionsSeeder::class);

    $customer = User::factory()->create();
    $customer->assignRole(RoleName::User->value);

    $payload = [
        'id_type' => 'national_id',
        'selfie' => UploadedFile::fake()->image('selfie.jpg'),
        'id_front' => UploadedFile::fake()->image('front.jpg'),
        'id_back' => UploadedFile::fake()->image('back.jpg'),
    ];

    $response = $this->actingAs($customer, 'sanctum')
        ->post('/api/v1/customer/verification', $payload);

    $response
        ->assertStatus(202)
        ->assertJsonPath('verification.status', 'pending');

    $this->assertDatabaseHas('user_identity_verifications', [
        'user_id' => $customer->id,
        'status' => UserIdentityVerificationStatus::Pending->value,
    ]);

    $duplicateResponse = $this->actingAs($customer, 'sanctum')
        ->post('/api/v1/customer/verification', $payload);

    $duplicateResponse
        ->assertStatus(422)
        ->assertJson(['message' => 'Your verification is already under review.']);
});
