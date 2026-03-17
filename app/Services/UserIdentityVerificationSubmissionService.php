<?php

namespace App\Services;

use App\Enums\UserIdentityVerificationStatus;
use App\Jobs\ProcessIdentityVerificationImage;
use App\Models\User;
use App\Models\UserIdentityVerification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class UserIdentityVerificationSubmissionService
{
    /**
     * @param  array{id_type:string,selfie:UploadedFile,id_front:UploadedFile,id_back:UploadedFile,is_adult?:mixed}  $data
     */
    public function submit(User $user, array $data): UserIdentityVerification
    {
        /** @var UserIdentityVerification $verification */
        $verification = DB::transaction(function () use ($user, $data) {
            $verification = UserIdentityVerification::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'id_type' => $data['id_type'],
                    'status' => UserIdentityVerificationStatus::Pending,
                    'is_age_confirmed' => (bool) ($data['is_adult'] ?? false),
                    'id_number' => null,
                    'date_of_birth' => null,
                    'district_id' => null,
                    'district_name' => null,
                    'county_id' => null,
                    'county_name' => null,
                    'sub_county_id' => null,
                    'sub_county_name' => null,
                    'parish_id' => null,
                    'parish_name' => null,
                    'village_id' => null,
                    'village_name' => null,
                    'submitted_at' => now(),
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                    'verified_at' => null,
                    'rejection_reason' => null,
                ]
            );

            foreach (['selfie', 'id_front', 'id_back'] as $collection) {
                $path = $data[$collection]->store('identity-verifications/tmp', 'local');

                ProcessIdentityVerificationImage::dispatch(
                    $verification->id,
                    $path,
                    $collection,
                    $data[$collection]->getClientOriginalName(),
                )->afterCommit();
            }

            return $verification;
        });

        return $verification;
    }
}
