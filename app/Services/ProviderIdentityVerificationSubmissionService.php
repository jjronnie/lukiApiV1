<?php

namespace App\Services;

use App\Enums\ProviderVerificationStatus;
use App\Jobs\ProcessProviderIdentityVerificationImage;
use App\Models\ProviderIdentityVerification;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ProviderIdentityVerificationSubmissionService
{
    /**
     * @param  array{id_type:string,selfie:UploadedFile,id_front:UploadedFile,id_back:UploadedFile,business_license?:UploadedFile|null}  $data
     */
    public function submit(User $user, array $data): ProviderIdentityVerification
    {
        /** @var ProviderIdentityVerification $verification */
        $verification = DB::transaction(function () use ($user, $data) {
            $profile = $user->providerProfile()->first();

            $verification = ProviderIdentityVerification::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'provider_profile_id' => $profile?->id,
                    'id_type' => $data['id_type'],
                    'status' => ProviderVerificationStatus::Pending,
                    'submitted_at' => now(),
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                    'verified_at' => null,
                    'rejection_reason' => null,
                ]
            );

            $collections = ['selfie', 'id_front', 'id_back'];
            if (($data['business_license'] ?? null) instanceof UploadedFile) {
                $collections[] = 'business_license';
            }

            foreach ($collections as $collection) {
                /** @var UploadedFile $file */
                $file = $data[$collection];
                $path = $file->store('provider-identity-verifications/tmp', 'local');

                ProcessProviderIdentityVerificationImage::dispatch(
                    $verification->id,
                    $path,
                    $collection,
                    $file->getClientOriginalName(),
                )->afterCommit();
            }

            if ($profile !== null) {
                $profile->update([
                    'verification_status' => ProviderVerificationStatus::Pending,
                    'rejection_reason' => null,
                ]);
            }

            return $verification;
        });

        return $verification;
    }
}
