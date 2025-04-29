<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Price;
use Illuminate\Console\Command;
use Faker\Factory as Faker;

class EnsureProductPrices extends Command
{
    protected $signature = 'prices:ensure-all {--chunk=500 : Number of products to process in one chunk}';
    protected $description = 'Ensures all products have at least one price record';

    public function handle()
    {
        // Zvýšení limitu paměti na 1GB
        ini_set('memory_limit', '1G');

        $chunkSize = (int)$this->option('chunk');

        $this->info("Counting products without prices...");
        $totalCount = Product::whereDoesntHave('prices')->count();

        $this->info("Found {$totalCount} products without price records");

        if ($totalCount === 0) {
            $this->info("All products already have prices. Nothing to do.");
            return Command::SUCCESS;
        }

        $faker = Faker::create();
        $bar = $this->output->createProgressBar($totalCount);
        $bar->start();

        // Zpracováváme produkty po částech (chunks) pro efektivnější správu paměti
        Product::whereDoesntHave('prices')
            ->chunk($chunkSize, function ($products) use ($faker, $bar) {
                $priceData = [];

                foreach ($products as $product) {
                    // Generujeme základní cenu podle typu produktu
                    if ($product->product_type === 'set') {
                        $basePrice = $product->num_parts
                            ? min(max($product->num_parts * 0.5, 10), 1000)
                            : $faker->randomFloat(2, 20, 500);
                        $condition = $faker->randomElement(['New', 'Used', 'Sealed']);
                    } else { // minifig nebo jiný typ
                        $basePrice = $faker->randomFloat(2, 1, 50);
                        $condition = $faker->randomElement(['Mint', 'Good', 'Played']);
                    }

                    // Přidáme do pole pro hromadné vložení
                    $priceData[] = [
                        'product_id' => $product->id,
                        'retail' => round($basePrice * 1.3, 2),
                        'wholesale' => round($basePrice * 0.7, 2),
                        'value' => round($basePrice, 2),
                        'condition' => $condition,
                        'type' => 'market',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    $bar->advance();
                }

                // Hromadné vložení cen
                Price::insert($priceData);

                // Vyčištění paměti
                unset($priceData);
                gc_collect_cycles();
            });

        $bar->finish();
        $this->newLine();
        $this->info("Successfully created price records for all products");

        return Command::SUCCESS;
    }
}
