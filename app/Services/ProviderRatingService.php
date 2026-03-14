<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ProviderProfile;

class ProviderRatingService
{
    public function refresh(ProviderProfile $profile): ProviderProfile
    {
        $ratedAverage = (float) Order::query()
            ->where('provider_profile_id', $profile->id)
            ->where('status', 'completed')
            ->whereNotNull('provider_rating')
            ->avg('provider_rating');

        $completedContribution = min(5.0, $profile->completed_orders_count * 0.2);
        $cancellationPenalty = min(2.5, $profile->cancelled_orders_count * 0.5);

        $baseScore = $ratedAverage > 0
            ? (($ratedAverage * 0.8) + ($completedContribution * 0.2))
            : $completedContribution;

        $rating = round(max(0, min(5, $baseScore - $cancellationPenalty)), 2);
        $ratingCount = (int) Order::query()
            ->where('provider_profile_id', $profile->id)
            ->whereNotNull('provider_rating')
            ->count();

        $profile->update([
            'rating_avg' => $rating,
            'rating_count' => $ratingCount,
        ]);

        return $profile->fresh() ?? $profile;
    }
}
