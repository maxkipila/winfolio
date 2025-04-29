<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Price;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class UpdateHistoricalPrices extends Command
{
    protected $signature = 'prices:update-historical';
    protected $description = 'Aktualizuje historické ceny produktů s variabilními hodnotami';

    public function handle()
    {
        $this->info("Začínám aktualizaci historických cen...");
        $faker = Faker::create();

        // Získáme všechny produkty s existující cenou
        Product::chunk(50, function ($products) use ($faker) {
            foreach ($products as $product) {
                $this->updateProductPrices($product, $faker);
            }
        });

        $this->info("Aktualizace historických cen dokončena.");
        return Command::SUCCESS;
    }

    private function updateProductPrices(Product $product, $faker)
    {
        // Získáme nejnovější cenu
        $latestPrice = Price::where('product_id', $product->id)
            ->latest('created_at')
            ->first();

        if (!$latestPrice) {
            return;
        }

        $baseValue = $latestPrice->value;
        $this->info("Aktualizuji historické ceny pro produkt #{$product->id} ({$product->name}) - základní cena: {$baseValue}");

        // Vytvoříme ceny za posledních 12 měsíců s variací
        $existingMonths = Price::where('product_id', $product->id)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month')
            ->distinct()
            ->pluck('month')
            ->toArray();

        for ($i = 1; $i <= 12; $i++) {
            $date = now()->subMonths($i)->startOfMonth();
            $month = $date->format('Y-m');

            // Přeskočíme měsíce, které už mají cenu
            if (in_array($month, $existingMonths)) {
                continue;
            }

            // Generujeme variabilní hodnotu v rozmezí ±15%
            $variationFactor = $faker->randomFloat(2, 0.85, 1.15);
            $value = round($baseValue * $variationFactor, 2);

            Price::create([
                'product_id' => $product->id,
                'value' => $value,
                'retail' => round($value * 1.3, 2),
                'wholesale' => round($value * 0.7, 2),
                'condition' => $latestPrice->condition,
                'type' => 'market',
                'created_at' => $date,
                'updated_at' => now(),
            ]);

            $this->line("  - Vytvořena cena pro {$month}: {$value}");
        }
    }
}
