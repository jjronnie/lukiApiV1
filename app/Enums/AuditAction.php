<?php

namespace App\Enums;

enum AuditAction: string
{
    case ProviderApproved = 'provider_approved';
    case ProviderRejected = 'provider_rejected';
    case WalletAdjusted = 'wallet_adjusted';
    case ServiceCategoryChanged = 'service_category_changed';
    case ServiceEdited = 'service_edited';
    case CommissionRuleChanged = 'commission_rule_changed';
    case DisputeResolved = 'dispute_resolved';
    case UserIdentityVerificationApproved = 'user_identity_verification_approved';
    case UserIdentityVerificationRejected = 'user_identity_verification_rejected';
}
