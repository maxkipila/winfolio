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

    protected $processedProducts = 0;
    protected $createdPrices = 0;

    public function handle()
    {
        $this->info("Aktualizace historicky cen...");

        ini_set('memory_limit', '1G');
        DB::disableQueryLog();

        $faker = Faker::create();
        $totalProducts = Product::count();

        $bar = $this->output->createProgressBar($totalProducts);
        $bar->start();

        $pricesToInsert = [];

        Product::chunk(50, function ($products) use ($faker, &$pricesToInsert, $bar) {
            foreach ($products as $product) {
                $latestPrice = Price::where('product_id', $product->id)
                    ->latest('created_at')
                    ->first();

                if (!$latestPrice) {
                    $bar->advance();
                    $this->processedProducts++;
                    continue;
                }

                $baseValue = $latestPrice->value;

                $existingMonths = Price::where('product_id', $product->id)
                    ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month')
                    ->distinct()
                    ->pluck('month')
                    ->toArray();

                for ($i = 1; $i <= 12; $i++) {
                    $date = now()->subMonths($i)->startOfMonth();
                    $month = $date->format('Y-m');

                    if (in_array($month, $existingMonths)) {
                        continue;
                    }

                    $variationFactor = $faker->randomFloat(2, 0.85, 1.15);
                    $value = round($baseValue * $variationFactor, 2);

                    $pricesToInsert[] = [
                        'product_id' => $product->id,
                        'value' => $value,
                        'retail' => round($value * 1.3, 2),
                        'wholesale' => round($value * 0.7, 2),
                        'condition' => $latestPrice->condition,
                        'type' => 'market',
                        'created_at' => $date,
                        'updated_at' => now(),
                    ];

                    $this->createdPrices++;
                }

                $this->processedProducts++;
                $bar->advance();

                if (count($pricesToInsert) >= 1000) {
                    Price::insert($pricesToInsert);
                    $pricesToInsert = [];
                }
            }
            gc_collect_cycles();
        });

        if (!empty($pricesToInsert)) {
            Price::insert($pricesToInsert);
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Aktualizace dokončena!");
        $this->info("Zpracováno {$this->processedProducts} produktů, vytvořeno {$this->createdPrices} cenových záznamů.");

        return Command::SUCCESS;
    }
}
