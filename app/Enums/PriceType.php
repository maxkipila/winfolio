<?php

namespace App\Enums;

enum PriceType: string
{
    case AGGREGATED = 'Aggregated';
    case SCRAPED = 'Scraped';
}
