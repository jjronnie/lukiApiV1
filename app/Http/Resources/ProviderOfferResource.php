<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderOfferResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'order_public_id' => $this->order->public_id,
            'status' => $this->status,
            'batch_no' => $this->batch_no,
            'expires_at' => $this->expires_at,
            'responded_at' => $this->responded_at,
            'meta' => $this->meta,
        ];
    }
}
