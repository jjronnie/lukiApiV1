<?php

namespace App\Enums;

enum VerificationSessionStatus: string
{
    case Open = 'open';
    case Submitted = 'submitted';
    case Expired = 'expired';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
