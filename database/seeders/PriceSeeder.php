<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Price;
use Faker\Factory as Faker;
use Carbon\Carbon;

class PriceSeeder extends Seeder
{
    public function run()
    {
        ini_set('memory_limit', '1G');

        $this->seedPrices();

        // Vyčištění paměti po seedování základních cen
        gc_collect_cycles();

        // Omezený počet produktů pro další operace
        $limitedProductIds = Product::limit(20)->pluck('id')->toArray();

        // Vyčištění paměti po získání ID
        unset($products);
        gc_collect_cycles();

        $this->simulateHistoricalPriceUpdates();

        // Vyčištění paměti po simulaci
        gc_collect_cycles();

        // Velmi omezený počet měsíců
        $this->generateAggregatedData(6);
    }

    /**
     * Seed prices for a specific product or all products
     *
     * @param Product|null $product Optional product to seed prices for
     */
    public function seedPrices(?Product $product = null)
    {
        DB::disableQueryLog();
        $faker = Faker::create();

        if ($product) {
            if (!Price::where('product_id', $product->id)->exists()) {
                $this->seedPriceForProduct($product, $faker);
            }
        } else {
            Product::chunk(100, function ($products) use ($faker) {
                $productIds = $products->pluck('id');
                $existingPriceIds = Price::whereIn('product_id', $productIds)
                    ->pluck('product_id')
                    ->toArray();

                $data = [];
                foreach ($products as $product) {
                    if (!in_array($product->id, $existingPriceIds)) {
                        $priceData = $this->generatePriceData($product, $faker);
                        $data[] = [
                            'product_id' => $product->id,
                            'retail'     => $priceData['retail'],
                            'wholesale'  => $priceData['wholesale'],
                            'value'      => $priceData['value'],
                            'condition'  => $priceData['condition'],
                            'type'       => $priceData['type'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }

                if (!empty($data)) {
                    DB::table('prices')->insert($data);
                }
            });
        }
    }

    /**
     * 
     */
    private function seedPriceForProduct(Product $product, $faker)
    {
        $priceData = $this->generatePriceData($product, $faker);

        DB::table('prices')->insert([
            'product_id' => $product->id,
            'retail'     => $priceData['retail'],
            'wholesale'  => $priceData['wholesale'],
            'value'      => $priceData['value'],
            'condition'  => $priceData['condition'],
            'type'       => $priceData['type'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Simuluje historické týdenní aktualizace cen.
     */
    /*  private function simulateHistoricalPriceUpdates()
    {
        $faker = Faker::create();
        $startDate = Carbon::now()->subDays(30);

        for ($i = 0; $i < 8; $i++) {
            $fakeDate = $startDate->copy()->addDays($i * 5);
            $this->weeklyPriceUpdate($fakeDate);
        }
    } */
    /*   private function simulateHistoricalPriceUpdates()
    {

        $offsets = [30, 25, 20, 15, 10, 5, 2, 1];
        foreach ($offsets as $offset) {
            $fakeDate = Carbon::now()->subDays($offset);
            $this->weeklyPriceUpdate($fakeDate);
        }
    } */
    private function simulateHistoricalPriceUpdates()
    {
        DB::disableQueryLog();

        // Vygenerujeme ceny pro poslední 3 měsíce pro 10 produktů
        $startDate = Carbon::now()->subMonths(3);
        $faker = Faker::create();

        $limitedProductIds = Product::limit(10)->pluck('id')->toArray();

        foreach ($limitedProductIds as $productId) {
            $product = Product::find($productId);

            for ($i = 0; $i < 3; $i++) {
                $date = $startDate->copy()->addMonths($i)->startOfMonth();
                $basePrice = 10 * pow(1.01, $i);

                DB::table('prices')->insert([
                    'product_id' => $product->id,
                    'retail'     => round($basePrice * 1.3, 2),
                    'wholesale'  => round($basePrice * 0.7, 2),
                    'value'      => round($basePrice, 2),
                    'condition'  => $product->product_type === 'set' ? 'New' : 'Mint',
                    'type'       => 'market',
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }
        }

        gc_collect_cycles();
    }


    /**
     * Generuje agregovaná historická data pro grafy
     */
    /**
     * Generuje minimální množství agregovaných dat pro testování
     */
    /**
     * Generuje agregovaná historická data pro grafy na základě reálných průměrů mediánů (TrendService).
     * Pro každý produkt a měsíc zavolá TrendService::getMonthlyAverageOfDailyMedians.
     * Pokud nejsou žádná denní data, agregace se přeskočí.
     */
    public function generateAggregatedData($months = 12)
    {
        DB::disableQueryLog();
        // TrendService je potřeba pro výpočet průměrů mediánů
        $trendService = app(\App\Services\TrendService::class);
        $now = Carbon::now();

        // Limitujte počet produktů na 50, chunkujte po 10
        Product::take(50)->chunk(10, function ($products) use ($trendService, $now, $months) {
            $aggregatedData = [];

            foreach ($products as $product) {
                for ($i = 0; $i < $months; $i++) {
                    $month = $now->copy()->subMonths($i)->startOfMonth();
                    // Výpočet reálného průměru mediánů za měsíc
                    $avg = $trendService->getMonthlyAverageOfDailyMedians($product->id, $month);
                    if ($avg === null) continue;

                    $aggregatedData[] = [
                        'product_id' => $product->id,
                        'value' => round($avg, 2),
                        'retail' => round($avg * 1.3, 2),
                        'wholesale' => round($avg * 0.7, 2),
                        'condition' => $product->product_type === 'set' ? 'New' : 'Mint',
                        'type' => 'aggregated',
                        'created_at' => $month,
                        'updated_at' => now(),
                    ];
                }
            }

            foreach (array_chunk($aggregatedData, 100) as $chunk) {
                DB::table('prices')->insert($chunk);
            }

            unset($aggregatedData);
            gc_collect_cycles();
        });
    }


    /**
     * 
     *
     * @param Carbon 
     */
    public function weeklyPriceUpdate(?Carbon $fakeDate = null, ?array $specificProductIds = null)
    {
        if (is_null($fakeDate)) {
            $fakeDate = Carbon::now();
        }

        $faker = Faker::create();
        $productQuery = Product::query();

        // Pokud jsou specifikovány konkrétní ID produktů, použijeme je
        if ($specificProductIds) {
            $productQuery->whereIn('id', $specificProductIds);
        }


        $productQuery->chunk(10, function ($products) use ($faker, $fakeDate) {
            $data = [];
            $productIds = $products->pluck('id');

            $latestPrices = Price::whereIn('product_id', $productIds)
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('product_id');

            foreach ($products as $product) {
                if (isset($latestPrices[$product->id]) && $latestPrices[$product->id]->isNotEmpty()) {
                    $latestPrice = $latestPrices[$product->id]->first();

                    if ($product->product_type === 'set') {
                        $variationFactor = $faker->randomFloat(nbMaxDecimals: 2, min: 0.97, max: 1.03);
                    } elseif ($product->product_type === 'minifig') {
                        $variationFactor = $faker->randomFloat(nbMaxDecimals: 2, min: 0.98, max: 1.02);
                    } else {
                        $variationFactor = $faker->randomFloat(nbMaxDecimals: 2, min: 0.97, max: 1.03);
                    }

                    $data[] = [
                        'product_id' => $product->id,
                        'retail'     => round($latestPrice->retail * $variationFactor, 2),
                        'wholesale'  => round($latestPrice->wholesale * $variationFactor, 2),
                        'value'      => round($latestPrice->value * $variationFactor, 2),
                        'condition'  => $latestPrice->condition,
                        'type'       => 'market', // Přidáno pole type
                        'created_at' => $fakeDate,
                        'updated_at' => $fakeDate,
                    ];
                }
            }

            // Insert po menších částech
            if (!empty($data)) {
                foreach (array_chunk($data, 20) as $chunk) {
                    DB::table('prices')->insert($chunk);
                }
            }

            // Uvolnění paměti
            unset($data, $latestPrices);
            gc_collect_cycles();
        });
    }

    /**
     * Vygeneruje realistická data cen pro produkt.
     */
    private function generatePriceData(Product $product, $faker): array
    {
        switch ($product->product_type) {
            case 'set':
                $basePrice = $product->num_parts
                    ? min(max($product->num_parts * 0.5, 10), 1000)
                    : $faker->randomFloat(2, 20, 500);
                $condition = $faker->randomElement(['New', 'Used', 'Sealed']);
                break;
            case 'minifig':
                $basePrice = $faker->randomFloat(2, 1, 50);
                $condition = $faker->randomElement(['Mint', 'Good', 'Played']);
                break;
            default:
                $basePrice = $faker->randomFloat(2, 10, 200);
                $condition = 'Unknown';
        }

        return [
            'retail'     => round($basePrice * 1.3, 2),
            'wholesale'  => round($basePrice * 0.7, 2),
            'value'      => round($basePrice, 2),
            'condition'  => $condition,
            'type'       => 'market', // Přidáno nové pole
        ];
    }
}
