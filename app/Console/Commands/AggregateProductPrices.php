<?php

namespace App\Console\Commands;

use App\Models\Price;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AggregateProductPrices extends Command
{
    protected $signature = 'prices:aggregate {--force} {--date=} {--days=31}';
    protected $description = 'Agregace cen produktu';

    public function handle()
    {

        //vypnut telescope
        if (class_exists(\Laravel\Telescope\Telescope::class)) {
            \Laravel\Telescope\Telescope::stopRecording();
        }

        ini_set('memory_limit', '2G');
        DB::disableQueryLog();
        $this->info('Start agreace...');

        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : Carbon::today()->subDay();

        $days = $this->option('days');
        $force = $this->option('force');

        //  pro tento den
        if (
            !$force && Price::where('type', 'aggregated')
            ->whereDate('created_at', $date)
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

        // TrendService Prumer dennich medianu 
        $trendService = app(\App\Services\TrendService::class);
        $month = $date->copy()->startOfMonth();

        Product::chunk(100, function ($products) use ($date, $days) {
            foreach ($products as $product) {
                $this->aggregateProductPrice($product, $date, $days);
            }
        });

        // ➕ BLOK pro měsíční mediány

        DB::disableQueryLog();
        gc_collect_cycles();

        $trendService = app(\App\Services\TrendService::class);
        $month = $date->copy()->startOfMonth();

        Product::chunk(100, function ($products) use ($trendService, $month) {
            foreach ($products as $product) {
                $value = $trendService->getMonthlyAverageOfDailyMedians($product->id, $month);
                if (!is_null($value)) {
                    Price::updateOrCreate([
                        'product_id' => $product->id,
                        'type' => 'aggregated',
                        'condition' => null,
                        'created_at' => $month,
                    ], [
                        'value' => $value,
                        'retail' => null,
                        'wholesale' => null,
                        'updated_at' => now(),
                    ]);
                }
            }
        });

        $this->info('Agregace cen dokončena.');
        return 0;
    }
    private function aggregateProductPrice(Product $product, Carbon $date, int $days)
    {
        $startDate = $date->copy()->subDays($days);
        $endDate = $date;

        // Všechny ceny produktu za období
        $prices = Price::where('product_id', $product->id)
            ->where('type', '!=', 'aggregated')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get(['value', 'retail', 'wholesale', 'condition', 'created_at']);

        $groupedByCondition = $prices->groupBy('condition');

        foreach ($groupedByCondition as $condition => $conditionPrices) {
            if ($conditionPrices->isEmpty()) {
                continue;
            }

            // Rozdělit ceny podle dne
            $dailyGroups = $conditionPrices->groupBy(function ($price) {
                return Carbon::parse($price->created_at)->toDateString();
            });

            $dailyMedians = [];
            $dailyRetailMedians = [];
            $dailyWholesaleMedians = [];

            foreach ($dailyGroups as $day => $prices) {
                // Value
                $values = $prices->pluck('value')->sort()->values()->toArray();
                $count = count($values);
                $middle = floor($count / 2);
                $median = $count % 2 === 0
                    ? ($values[$middle - 1] + $values[$middle]) / 2
                    : $values[$middle];
                $dailyMedians[] = $median;

                // Retail
                $retailValues = $prices->pluck('retail')->sort()->values()->toArray();
                $retailCount = count($retailValues);
                $retailMiddle = floor($retailCount / 2);
                $retailMedian = $retailCount % 2 === 0
                    ? ($retailValues[$retailMiddle - 1] + $retailValues[$retailMiddle]) / 2
                    : $retailValues[$retailMiddle];
                $dailyRetailMedians[] = $retailMedian;

                // Wholesale
                $wholesaleValues = $prices->pluck('wholesale')->sort()->values()->toArray();
                $wholesaleCount = count($wholesaleValues);
                $wholesaleMiddle = floor($wholesaleCount / 2);
                $wholesaleMedian = $wholesaleCount % 2 === 0
                    ? ($wholesaleValues[$wholesaleMiddle - 1] + $wholesaleValues[$wholesaleMiddle]) / 2
                    : $wholesaleValues[$wholesaleMiddle];
                $dailyWholesaleMedians[] = $wholesaleMedian;
            }

            if (empty($dailyMedians)) {
                return;
            }

            $medianValue = round(array_sum($dailyMedians) / count($dailyMedians), 2);
            $retailMedian = round(array_sum($dailyRetailMedians) / count($dailyRetailMedians), 2);
            $wholesaleMedian = round(array_sum($dailyWholesaleMedians) / count($dailyWholesaleMedians), 2);

            Price::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'type' => 'aggregated',
                    'condition' => $condition,
                    'created_at' => $date->copy()->startOfDay(),
                ],
                [
                    'value' => $medianValue,
                    'retail' => $retailMedian,
                    'wholesale' => $wholesaleMedian,
                    'updated_at' => now(),
                ]
            );

            $this->line("Agregována cena pro produkt {$product->id}, stav {$condition}: hodnota={$medianValue}, retail={$retailMedian}, wholesale={$wholesaleMedian}");
        }
    }
    /* private function aggregateProductPrice(Product $product, Carbon $date, int $days)
    {
        $startDate = $date->copy()->subDays($days);
        $endDate = $date;

        //  produkt za  období
        $prices = Price::where('product_id', $product->id)
            ->where('type', '!=', 'aggregated')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get(['value', 'retail', 'wholesale', 'condition', 'created_at']);

        $groupedByCondition = $prices->groupBy('condition');

        foreach ($groupedByCondition as $condition => $conditionPrices) {
            if ($conditionPrices->isEmpty()) {
                continue;
            }

            // Výpočet mediánu pro value
            $values = $conditionPrices->pluck('value')->toArray();
            sort($values);
            $count = count($values);
            $middle = floor($count / 2);
            $medianValue = $count % 2 === 0
                ? ($values[$middle - 1] + $values[$middle]) / 2
                : $values[$middle];

            // Výpočet mediánu pro retail
            $retailValues = $conditionPrices->pluck('retail')->toArray();
            sort($retailValues);
            $retailMedian = $count % 2 === 0
                ? ($retailValues[$middle - 1] + $retailValues[$middle]) / 2
                : $retailValues[$middle];

            // Výpočet mediánu pro wholesale
            $wholesaleValues = $conditionPrices->pluck('wholesale')->toArray();
            sort($wholesaleValues);
            $wholesaleMedian = $count % 2 === 0
                ? ($wholesaleValues[$middle - 1] + $wholesaleValues[$middle]) / 2
                : $wholesaleValues[$middle];

            Price::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'type' => 'aggregated',
                    'condition' => $condition,
                    'created_at' => $date->copy()->startOfDay(),
                ],
                [
                    'value' => $medianValue,
                    'retail' => $retailMedian,
                    'wholesale' => $wholesaleMedian,
                    'updated_at' => now(),
                ]
            );

            $this->line("Agregována cena pro produkt {$product->id}, stav {$condition}: hodnota={$medianValue}, retail={$retailMedian}, wholesale={$wholesaleMedian}");
        }
    } */
}
