<?php

namespace App\Enums;

enum PriceType: string
{
    case AGGREGATED = 'aggregated';
    case SCRAPED = 'scraped';
}
