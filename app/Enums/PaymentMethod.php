<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Card = 'card';
    case Mtn = 'mtn';
    case Airtel = 'airtel';
}
