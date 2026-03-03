<?php

namespace App\Enums;

enum DisputeCategory: string
{
    case ServiceQuality = 'service_quality';
    case Overcharge = 'overcharge';
    case NoShow = 'no_show';
    case Safety = 'safety';
    case Other = 'other';
}
