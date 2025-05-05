<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'retail',
        'wholesale',
        'value',
        'condition',
        'type',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];


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
    public function scopeAggregated($query)
    {
        return $query->where('type', 'aggregated');
    }
    public function scopeIndividual($query)
    {
        return $query->where('type', '!=', 'aggregated');
    }

    /**
     * Scope to find prices by currency
     */
    public function scopeByCurrency($query, $currency)
    {
        return $query->where('currency', $currency);
    }

    public function getPlatformAttribute()
    {
        $metadata = $this->metadata;
        return $metadata['platform'] ?? null;
    }

    public function getDescriptionAttribute()
    {
        $metadata = $this->metadata;
        return $metadata['description'] ?? null;
    }

    public function getChangeAttribute()
    {
        $metadata = $this->metadata;
        return $metadata['change'] ?? null;
    }

    public function getLinkAttribute()
    {
        $metadata = $this->metadata;
        return $metadata['link'] ?? null;
    }
}
