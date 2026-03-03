<?php

namespace App\Enums;

enum PricingRuleType: string
{
    case DistancePerKm = 'distance_per_km';
    case DistanceBand = 'distance_band';
    case TaxPercentage = 'tax_percentage';
    case PeakHours = 'peak_hours';
    case Overtime = 'overtime';
}
