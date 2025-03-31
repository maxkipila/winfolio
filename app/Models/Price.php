<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    use HasFactory;
    protected $guarded = [];/* 
    protected $primaryKey = 'price_id';
    protected $keyType = 'string';
    public $incrementing = false; */

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope to find prices for a specific product
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope to find prices by condition
     */
    public function scopeByCondition($query, $condition)
    {
        return $query->where('condition', $condition);
    }

    /**
     * Scope to find prices by currency
     */
    public function scopeByCurrency($query, $currency)
    {
        return $query->where('currency', $currency);
    }
}
