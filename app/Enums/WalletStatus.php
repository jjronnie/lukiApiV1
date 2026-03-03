<?php

namespace App\Enums;

enum WalletStatus: string
{
    case Active = 'active';
    case Blocked = 'blocked';
}
