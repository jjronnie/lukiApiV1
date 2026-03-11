<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderPreviewResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isOnline = (bool) ($this->availability?->is_online ?? false);
        $isRecentlySeen = $this->availability?->last_seen_at !== null
            && $this->availability->last_seen_at->gte(now()->subMinutes(2));

        return [
            'public_id' => $this->public_id,
            'display_name' => $this->display_name,
            'provider_number' => $this->provider_number,
            'avatar_url' => $this->avatar_url,
            'phone' => $this->user?->phone,
            'rating_avg' => (float) $this->rating_avg,
            'reviews_count' => (int) $this->rating_count,
            'verification_status' => $this->verification_status?->value ?? $this->verification_status,
            'is_available' => $isOnline && $isRecentlySeen,
            'availability_label' => $isOnline && $isRecentlySeen
                ? 'Available now'
                : 'Unavailable right now',
        ];
    }
}
