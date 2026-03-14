<?php

namespace App\Enums;

enum VerificationSessionFlow: string
{
    case CustomerIdentity = 'customer_identity';
    case ProviderIdentity = 'provider_identity';
}
