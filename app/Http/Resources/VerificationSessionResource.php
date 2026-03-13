<?php

namespace App\Http\Resources;

use App\Enums\VerificationSessionStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VerificationSessionResource extends JsonResource
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
            'expires_at' => $this->expires_at,
            'started_at' => $this->started_at,
            'submitted_at' => $this->submitted_at,
            'completed_at' => $this->completed_at,
            'is_active' => $status === VerificationSessionStatus::Open->value
                && $this->expires_at !== null
                && $this->expires_at->isFuture(),
            'is_expired' => $status === VerificationSessionStatus::Expired->value
                || ($this->expires_at !== null && $this->expires_at->isPast()),
        ];
    }
}
