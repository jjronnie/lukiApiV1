<?php

namespace App\Enums;

enum RoleName: string
{
    case Superadmin = 'superadmin';
    case Admin = 'admin';
    case Provider = 'provider';
    case User = 'user';
}
