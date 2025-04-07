<?php

namespace App\Enums;

enum PriceCondition: string
{
    case NEW = 'New';
    case USED = 'Used';
    case SEALED = 'Sealed';
    case MINT = 'Mint';
    case GOOD = 'Good';
    case PLAYED = 'Played';
    case UNKNOWN = 'Unknown';
}
