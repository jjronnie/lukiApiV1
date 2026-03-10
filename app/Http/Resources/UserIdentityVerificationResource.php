<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserIdentityVerificationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $status = $this->status?->value ?? $this->status;

        return [
            'public_id' => $this->public_id,
            'status' => $status,
            'id_type' => $this->id_type,
            'submitted_at' => $this->submitted_at,
            'reviewed_at' => $this->reviewed_at,
            'verified_at' => $this->verified_at,
            'rejection_reason' => $this->rejection_reason,
            'is_pending' => $status === 'pending',
            'is_verified' => $status === 'approved',
            'can_retry' => in_array($status, ['rejected'], true),
        ];
    }
}
