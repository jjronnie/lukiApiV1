<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Created = 'created';
    case Offering = 'offering';
    case Accepted = 'accepted';
    case OnTheWay = 'on_the_way';
    case Arrived = 'arrived';
    case InService = 'in_service';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    public function isTerminal(): bool
    {
        return in_array($this, [self::Completed, self::Cancelled, self::Expired], true);
    }
}
