<?php

namespace App\Enums;

enum UserIdentityVerificationStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
