<?php

namespace App\Enums;



enum AwardConditionType: string
{
    case SpecificProduct = 'specific_product';
    case SpecificCategory = 'specific_category';
    case CategoryItemsCount = 'category_items_count';
    case TotalItemsCount = 'total_items_count';
    case PortfolioValue = 'portfolio_value';
    case PortfolioPercentage = 'portfolio_percentage';
}
