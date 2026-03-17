<?php

namespace App\Enums;

enum ProviderServiceApprovalStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Declined = 'declined';
    case Suspended = 'suspended';
}
