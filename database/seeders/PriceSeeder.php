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

        gc_collect_cycles();

        $this->createHistoricalPriceTimeline();

        gc_collect_cycles();

        // Generování agregovaných dat
        // $this->generateAggregatedData(12);
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

                $basePrice = $this->generatePriceData($product, $faker)['value'];

                for ($i = 1; $i <= 12; $i++) {
                    $variationFactor = $faker->randomFloat(2, 0.85, 1.15); // 15% variace
                    $date = now()->subMonths($i);

                    DB::table('prices')->insert([
                        'product_id' => $product->id,
                        'retail' => round($basePrice * 1.3 * $variationFactor, 2),
                        'wholesale' => round($basePrice * 0.7 * $variationFactor, 2),
                        'value' => round($basePrice * $variationFactor, 2),
                        'condition' => $product->product_type === 'set' ? 'New' : 'Mint',
                        'type' => 'market',
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);
                }
            }
        } else {

            $chunkSize = 50;

            Product::whereDoesntHave('prices')
                ->chunkById($chunkSize, function ($products) use ($faker) {
                    $priceData = [];

                    foreach ($products as $product) {

                        $priceInfo = $this->generatePriceData($product, $faker);

                        $priceData[] = [
                            'product_id' => $product->id,
                            'retail' => $priceInfo['retail'],
                            'wholesale' => $priceInfo['wholesale'],
                            'value' => $priceInfo['value'],
                            'condition' => $priceInfo['condition'],
                            'type' => $priceInfo['type'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    // Vkládáme po menších částech pro lepší stabilitu
                    foreach (array_chunk($priceData, 50) as $chunk) {
                        DB::table('prices')->insert($chunk);
                    }

                    // Vyčištění paměti
                    unset($priceData);
                    gc_collect_cycles();
                });
        }
    }

    /**
     * Vytvoří historické ceny pro 50 produktů za poslední 3 roky
     * s realistickým trendem
     */
    private function createHistoricalPriceTimeline()
    {
        DB::disableQueryLog();
        $faker = Faker::create();

        // Zvýšíme počet produktů pro zpracování
        $products = Product::whereHas('prices')->limit(500)->get();

        foreach ($products as $product) {
            // Definice specifických datumů pro různá časová období
            $timepoints = [
                Carbon::now()->subDays(7),                 // týden zpět
                Carbon::now()->subDays(30),                // měsíc zpět
                Carbon::now()->subMonths(3),               // 3 měsíce zpět
                Carbon::now()->subMonths(6),               // 6 měsíců zpět
                Carbon::now()->subYear(),                  // rok zpět
                Carbon::now()->subYear()->subMonths(6),    // 1.5 roku zpět 
                Carbon::now()->subYears(2),                // 2 roky zpět
                Carbon::now()->subYears(3)                 // 3 roky zpět
            ];

            // Rozhodnutí o celkovém trendu ceny
            $trendType = $faker->randomElement(['rising', 'falling', 'volatile', 'stable']);
            $latestPrice = Price::where('product_id', $product->id)->latest('created_at')->first();

            if (!$latestPrice) {
                continue;
            }

            $baseValue = $latestPrice->value;
            $priceData = [];

            // Pro každý časový bod vytváříme cenový záznam
            foreach ($timepoints as $index => $date) {
                // Aplikujeme trend různě podle typu trendu
                $trendFactor = $this->calculateTrendFactor($trendType, $index, count($timepoints), $faker);

                // Hodnoty ceny
                $value = round($baseValue * $trendFactor, 2);
                $retail = round($value * 1.3, 2);
                $wholesale = round($value * 0.7, 2);

                $priceData[] = [
                    'product_id' => $product->id,
                    'retail'     => $retail,
                    'wholesale'  => $wholesale,
                    'value'      => $value,
                    'condition'  => $latestPrice->condition,
                    'type'       => 'market',
                    'created_at' => $date,
                    'updated_at' => $date,
                ];
            }

            // Ukládáme ceny po dávkách
            DB::table('prices')->insert($priceData);

            // Čištění paměti
            unset($priceData);
            gc_collect_cycles();
        }
    }


    /**
     * Vypočítá faktor trendu ceny podle typu trendu
     */
    private function calculateTrendFactor($trendType, $index, $totalPoints, $faker)
    {
        $position = $index / ($totalPoints - 1); // 0 = nejnovější, 1 = nejstarší

        switch ($trendType) {
            case 'rising':
                // Starší ceny jsou nižší (klesáme jak jdeme do minulosti)
                return 1 - ($position * $faker->randomFloat(2, 0.2, 0.5));

            case 'falling':
                // Starší ceny jsou vyšší (stoupáme jak jdeme do minulosti)
                return 1 + ($position * $faker->randomFloat(2, 0.1, 0.4));

            case 'volatile':
                // Ceny náhodně kolísají
                $baseTrend = 1 - ($position * 0.1); // Mírný pokles do minulosti
                $volatility = $faker->randomFloat(2, -0.2, 0.2);
                return $baseTrend + $volatility;

            case 'stable':
                // Ceny se mění jen minimálně
                return 1 + ($faker->randomFloat(2, -0.05, 0.05));

            default:
                return 1;
        }
    }

    /**
     * Seed price for a specific product
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
     * Generuje agregovaná historická data pro grafy na základě reálných průměrů mediánů (TrendService).
     */
    public function generateAggregatedData($months = 12)
    {
        DB::disableQueryLog();

        $trendService = app(\App\Services\TrendService::class);
        $now = Carbon::now();

        // Zvýšíme počet produktů pro zpracování
        Product::chunkById(50, function ($products) use ($trendService, $now, $months) {
            $aggregatedData = [];

            foreach ($products as $product) {
                for ($i = 0; $i < $months; $i++) {
                    $month = $now->copy()->subMonths($i)->startOfMonth();

                    $avg = $trendService->getMonthlyAverageOfDailyMedians($product->id, $month);
                    if ($avg === null) {
                        // Fallback to the closest available price if no daily medians exist
                        $avg = $trendService->getMedianPriceForProduct($product->id, $month->toDateString());
                    }
                    if ($avg === null) {
                        // Still no data, skip this month
                        continue;
                    }

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
     * Weekly price update simulation
     *
     * @param Carbon $fakeDate
     */
    public function weeklyPriceUpdate(?Carbon $fakeDate = null, ?array $specificProductIds = null)
    {
        if (is_null($fakeDate)) {
            $fakeDate = Carbon::now();
        }

        $faker = Faker::create();
        $productQuery = Product::query();

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

                    // Realističtější variace podle typu produktu
                    if ($product->product_type === 'set') {
                        // Sety mohou mít větší cenové skoky
                        $variationFactor = $faker->randomFloat(nbMaxDecimals: 2, min: 0.95, max: 1.05);
                    } elseif ($product->product_type === 'minifig') {
                        // Minifigurky mají obecně stabilnější ceny
                        $variationFactor = $faker->randomFloat(nbMaxDecimals: 2, min: 0.98, max: 1.03);
                    } else {
                        $variationFactor = $faker->randomFloat(nbMaxDecimals: 2, min: 0.97, max: 1.03);
                    }

                    $data[] = [
                        'product_id' => $product->id,
                        'retail'     => round($latestPrice->retail * $variationFactor, 2),
                        'wholesale'  => round($latestPrice->wholesale * $variationFactor, 2),
                        'value'      => round($latestPrice->value * $variationFactor, 2),
                        'condition'  => $latestPrice->condition,
                        'type'       => 'market',
                        'created_at' => $fakeDate,
                        'updated_at' => $fakeDate,
                    ];
                }
            }

            if (!empty($data)) {
                foreach (array_chunk($data, 20) as $chunk) {
                    DB::table('prices')->insert($chunk);
                }
            }


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
            'type'       => 'market',
        ];
    }

    public function seedHistoricalPrices()
    {
        $products = Product::all();

        foreach ($products as $product) {
            // Vytvoření základní ceny pro produkt
            $basePrice = $this->getBasePrice($product);

            // Vytvoření historických záznamů
            $this->createHistoricalPrices($product->id, $basePrice);
        }
    }
    private function getBasePrice($product)
    {
        // Pokud už existuje cena, použijeme ji jako základ
        $existingPrice = Price::where('product_id', $product->id)
            ->latest('created_at')
            ->first();

        if ($existingPrice) {
            return $existingPrice->value;
        }

        // Jinak vygenerujeme podle typu produktu
        return match ($product->product_type) {
            'set' => $product->num_parts ? max(10, $product->num_parts * 0.5) : rand(20, 500),
            'minifig' => rand(1, 50),
            default => rand(10, 200)
        };
    }

    private function createHistoricalPrices($productId, $basePrice)
    {
        // Create a realistic trend (growth or decline)
        $trendType = rand(0, 100) < 70 ? 'growth' : 'decline'; // 70% chance of growth
        $trendStrength = rand(5, 20) / 100; // 5-20% overall trend

        // Volatility - how much random variation
        $volatility = rand(3, 10) / 100; // 3-10% volatility

        // Create prices for the last 12 months
        for ($i = 12; $i >= 0; $i--) {
            $month = now()->subMonths($i);

            // Calculate trend component
            $trendFactor = $trendType === 'growth'
                ? 1 + ($trendStrength * (12 - $i) / 12)
                : 1 - ($trendStrength * (12 - $i) / 12);

            // Add random volatility
            $randomFactor = 1 + (rand(-100, 100) / 100 * $volatility);

            // Final price with trend and randomness
            $price = $basePrice * $trendFactor * $randomFactor;

            // Ensure the price stays positive and reasonable
            $price = max(0.01, min($price, $basePrice * 3));

            Price::updateOrCreate(
                [
                    'product_id' => $productId,
                    'type' => 'aggregated',
                    'created_at' => $month->startOfMonth(),
                ],
                [
                    'value' => round($price, 2),
                    'retail' => round($price * 1.3, 2),
                    'wholesale' => round($price * 0.7, 2),
                    'condition' => 'New', // Or use product type to determine
                ]
            );
        }

        /*  private function createHistoricalPrices($productId, $basePrice)
    {
        
        $trendType = rand(0, 100) < 70 ? 'growth' : 'decline'; // 70% chance of growth
        $trendStrength = rand(5, 20) / 100; // 5-20% overall trend

        
        $volatility = rand(3, 10) / 100; // 3-10% volatility

    
        for ($i = 12; $i >= 0; $i--) {
            $month = now()->subMonths($i);

      
            $trendFactor = $trendType === 'growth'
                ? 1 + ($trendStrength * (12 - $i) / 12)
                : 1 - ($trendStrength * (12 - $i) / 12);

        
            $randomFactor = 1 + (rand(-100, 100) / 100 * $volatility);

         
            $price = $basePrice * $trendFactor * $randomFactor;

           
            $price = max(0.01, min($price, $basePrice * 3));

            Price::updateOrCreate(
                [
                    'product_id' => $productId,
                    'type' => 'aggregated',
                    'created_at' => $month->startOfMonth(),
                ],
                [
                    'value' => round($price, 2),
                    'retail' => round($price * 1.3, 2),
                    'wholesale' => round($price * 0.7, 2),
                    'condition' => 'New', 
                ]
            );
        }
    } */
    }
}
