<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRecord extends Model
{
    use HasFactory;

    /**
     * Atributy, které lze hromadně přiřazovat.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'record_type',
        'value',
        'count',
        'percentage',
        'product_id',
    ];

    /**
     * Konstanty pro typy rekordů
     */
    const TYPE_HIGHEST_PORTFOLIO_VALUE = 'highest_portfolio_value';
    const TYPE_MOST_ITEMS = 'most_items';
    const TYPE_BEST_PURCHASE = 'best_purchase';
    const TYPE_WORST_PURCHASE = 'worst_purchase';

    /**
     * Získá uživatele, kterému tento rekord patří.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Získá produkt spojený s tímto rekordem (pokud existuje).
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
