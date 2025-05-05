<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegoIdMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'rebrickable_id',
        'brickeconomy_id',
        'bricklink_id',
        'name',
        'notes',
    ];

    /**
     * Přidá nebo aktualizuje mapování ID
     */
    public static function addMapping(
        string $rebrickableId,
        ?string $brickeconomyId = null,
        ?string $bricklinkId = null,
        ?string $name = null,
        ?string $notes = null
    ): self {
        return self::updateOrCreate(
            ['rebrickable_id' => $rebrickableId],
            [
                'brickeconomy_id' => $brickeconomyId,
                'bricklink_id' => $bricklinkId,
                'name' => $name,
                'notes' => $notes,
            ]
        );
    }
}
