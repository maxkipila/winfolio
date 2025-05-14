<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegoIdMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'brickeconomy_id',
    ];

    /**
     * Vztah k produktu
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Statická metoda pro přidání nebo aktualizaci mapování
     */
    public static function addMapping(int $productId, ?string $brickeconomyId = null): self
    {
        return self::updateOrCreate(
            ['product_id' => $productId],
            [
                'brickeconomy_id' => $brickeconomyId,
            ]
        );
    }
}
