<?php

namespace App\Services;

use App\Models\LegoIdMapping;
use App\Models\Product;
use App\Models\Theme;

class LegoIdAdapter
{
    /**
     * Známé prefixy podle témat pro mapování
     * @var array
     */
    protected static $themePrefix = [
        'Star Wars' => 'sw',
        'Harry Potter' => 'hp',
        'Batman' => 'bat',
        'Marvel' => 'sh',
        'Super Heroes' => 'sh',
        'Ninjago' => 'njo',
        'Lord of the Rings' => 'lor',
        'City' => 'cty',
        'Town' => 'twn',
        // další mapování...
    ];

    /**
     * Převede Rebrickable ID na BrickEconomy ID
     */
    public static function toBrickEconomy(string $rebrickableId): ?string
    {
        // Nejprve zkusíme najít v tabulce mapování
        $mapping = LegoIdMapping::where('rebrickable_id', $rebrickableId)->first();
        if ($mapping && $mapping->brickeconomy_id) {
            return $mapping->brickeconomy_id;
        }

        // Logika převádění ID podle pravidel
        // Tuto logiku můžeme převzít z BrickEconomyScraper::determineProductType

        return null;
    }

    /**
     * Převede BrickEconomy ID na Rebrickable ID
     */
    public static function toRebrickable(string $brickEconomyId): ?string
    {
        // Mapování opačným směrem
        $mapping = LegoIdMapping::where('brickeconomy_id', $brickEconomyId)->first();
        if ($mapping) {
            return $mapping->rebrickable_id;
        }

        return null;
    }

    /**
     * Převede BrickLink ID na Rebrickable ID
     */
    public static function brickLinkToRebrickable(string $brickLinkId): ?string
    {
        $mapping = LegoIdMapping::where('bricklink_id', $brickLinkId)->first();
        if ($mapping) {
            return $mapping->rebrickable_id;
        }

        return null;
    }

    /**
     * Vytvoří mapování mezi ID
     */
    public static function createMapping(string $rebrickableId, ?string $brickEconomyId = null, ?string $brickLinkId = null, ?string $name = null): LegoIdMapping
    {
        return LegoIdMapping::updateOrCreate(
            ['rebrickable_id' => $rebrickableId],
            [
                'brickeconomy_id' => $brickEconomyId,
                'bricklink_id' => $brickLinkId,
                'name' => $name
            ]
        );
    }
}
