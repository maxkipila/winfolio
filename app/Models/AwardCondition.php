<?php

namespace App\Models;

use App\Enums\AwardConditionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AwardCondition extends Model
{
    use HasFactory;

    /**
     * Atributy, které lze hromadně přiřazovat.
     *
     * @var array
     */
    protected $guarded = [];
    protected $casts = [
        'condition_type' => AwardConditionType::class,
    ];

    //podminky
    const TYPE_SPECIFIC_PRODUCT = 'specific_product';

    //uživatel musi přidat konkrétní kategorii.
    const TYPE_SPECIFIC_CATEGORY = 'specific_category';
    //uživatel musi přidat konkrétní počet produktů.
    const TYPE_CATEGORY_ITEMS_COUNT = 'category_items_count';
    //uživatel musi přidat konkrétní počet produktů.
    const TYPE_TOTAL_ITEMS_COUNT = 'total_items_count';
    //uživatel musi přidat konkrétní hodnotu portfolia.
    const TYPE_PORTFOLIO_VALUE = 'portfolio_value';
    //uživatel musi přidat konkrétní hodnotu portfolia.
    const TYPE_PORTFOLIO_PERCENTAGE = 'portfolio_percentage';

    /**
     * Získá odznak, ke kterému tato podmínka patří.
     */
    public function award(): BelongsTo
    {
        return $this->belongsTo(Award::class);
    }

    /**
     * Získá produkt spojený s touto podmínkou (pokud existuje).
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Získá kategorii spojenou s touto podmínkou (pokud existuje).
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
