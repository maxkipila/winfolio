<?php

namespace App\Console\Commands;

use App\Models\Price;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AggregateProductPrices extends Command
{
    protected $signature = 'prices:aggregate {--force}';
    protected $description = 'Agregace cen produktu';

    public function handle()
    {
        // Vypnutí Telescope pro optimalizaci
        if (class_exists(\Laravel\Telescope\Telescope::class)) {
            \Laravel\Telescope\Telescope::stopRecording();
        }

        // Zvýšení limitu paměti pro zpracování většího počtu dat
        ini_set('memory_limit', '2G');
        DB::disableQueryLog();
        $this->info('Start agregace...');

        // Vždy používáme včerejší datum jako výchozí
        $date = Carbon::today()->subDay();
        $days = 31; // Standardně agregujeme za posledních 31 dní
        $force = $this->option('force');

        // Kontrola, zda již existují agregovaná data pro tento den 
        if (
            !$force && Price::where('date', $date->toDateString())
            ->where('type', 'aggregated')
            ->where('value', '>', 0)
            ->exists()
        ) {
            $this->info("Pro datum {$date->toDateString()} již agregovaná data existují. Použijte --force pro přepsání.");
            return 0;
        }

        // Získáme všechny produkty
        Product::chunk(100, function ($products) use ($date, $days) {
            foreach ($products as $product) {
                $this->aggregateProductPrice($product, $date, $days);
            }
        });

        // Měsíční agregace
        $this->info('Počítám měsíční průměry...');
        $month = $date->copy()->startOfMonth();
        $trendService = app(\App\Services\TrendService::class);

        $bar = $this->output->createProgressBar(Product::count());
        $bar->start();

        Product::chunk(100, function ($products) use ($trendService, $month, $bar) {
            foreach ($products as $product) {
                $value = $trendService->getMonthlyAverageOfDailyMedians($product->id, $month);
                if (!is_null($value)) {
                    Price::updateOrCreate([
                        'product_id' => $product->id,
                        'date' => $month->toDateString(),
                        'type' => 'aggregated',
                    ], [
                        'value' => $value,
                        'retail' => round($value * 1.3, 2),
                        /* 'condition' => 'New', */
                        'currency' => 'EUR',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                $bar->advance();
            }

            // Uvolníme paměť
            gc_collect_cycles();
        });

        $bar->finish();
        $this->newLine();

        $this->info('Agregace cen dokončena.');
        return 0;
    }

    private function aggregateProductPrice(Product $product, Carbon $date, int $days)
    {
        $startDate = $date->copy()->subDays($days);
        $endDate = $date;

        // Všechny ceny produktu za období - pouze "Scraped" typ
        $prices = Price::where('product_id', $product->id)
            ->where('type', 'Scraped')
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get(['value', 'date']);

        if ($prices->isEmpty()) {
            return;
        }

        // Rozdělit ceny podle dne
        $dailyGroups = $prices->groupBy(function ($price) {
            return Carbon::parse($price->date)->toDateString();
        });

        $dailyMedians = [];

        foreach ($dailyGroups as $day => $dayPrices) {
            // Value
            $values = $dayPrices->pluck('value')->sort()->values()->toArray();
            if (empty($values)) continue;

            $count = count($values);
            $middle = floor($count / 2);
            $median = $count % 2 === 0
                ? ($values[$middle - 1] + $values[$middle]) / 2
                : $values[$middle];
            $dailyMedians[] = $median;
        }

        if (empty($dailyMedians)) {
            return;
        }

        $medianValue = round(array_sum($dailyMedians) / count($dailyMedians), 2);

        Price::updateOrCreate(
            [
                'product_id' => $product->id,
                'date' => $date->toDateString(),
                'type' => 'aggregated',
            ],
            [
                'value' => $medianValue,
                'retail' => round($medianValue * 1.3, 2),
                /* 'condition' => 'New', */
                'currency' => 'EUR',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
