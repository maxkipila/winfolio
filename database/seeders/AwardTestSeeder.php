<?php

namespace Database\Seeders;

use App\Models\Award;
use App\Models\AwardCondition;
use Illuminate\Database\Seeder;

class AwardTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Odznak za vlastnictví konkrétního produktu (Test Minifig 1)
        $minifigAward = Award::create([
            'name' => 'Sběratel figurek',
            'type' => 'collection',
            'category' => 'basic',
            'description' => 'Získáte za přidání figurky Test Minifig 1 do své sbírky',
            'icon' => 'minifig'
        ]);

        AwardCondition::create([
            'award_id' => $minifigAward->id,
            'condition_type' => 'specific_product',
            'product_id' => 5,  // ID produktu Test Minifig 1
            'required_count' => null,
            'required_value' => null,
            'required_percentage' => null
        ]);

        // 2. Odznak za určitý počet položek v portfoliu
        $collectorAward = Award::create([
            'name' => 'Začínající sběratel',
            'type' => 'collection',
            'category' => 'basic',
            'description' => 'Získáte za vlastnictví alespoň 5 položek',
            'icon' => 'collection'
        ]);

        AwardCondition::create([
            'award_id' => $collectorAward->id,
            'condition_type' => 'total_items_count',
            'product_id' => null,
            'category_id' => null,
            'required_count' => 5,
            'required_value' => null,
            'required_percentage' => null
        ]);

        // 3. Odznak za určitou hodnotu portfolia
        $valueAward = Award::create([
            'name' => 'Hodnotná sbírka',
            'type' => 'value',
            'category' => 'advanced',
            'description' => 'Získáte za dosažení hodnoty portfolia 300+',
            'icon' => 'money'
        ]);

        AwardCondition::create([
            'award_id' => $valueAward->id,
            'condition_type' => 'portfolio_value',
            'product_id' => null,
            'category_id' => null,
            'required_count' => null,
            'required_value' => 300,
            'required_percentage' => null
        ]);

        // 4. Odznak za zhodnocení portfolia
        $growthAward = Award::create([
            'name' => 'Investor',
            'type' => 'growth',
            'category' => 'advanced',
            'description' => 'Získáte za zhodnocení portfolia o 200%',
            'icon' => 'chart'
        ]);

        AwardCondition::create([
            'award_id' => $growthAward->id,
            'condition_type' => 'portfolio_percentage',
            'product_id' => null,
            'category_id' => null,
            'required_count' => null,
            'required_value' => null,
            'required_percentage' => 200
        ]);
    }
}
