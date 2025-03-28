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
        $this->seedPrices();

        $this->simulateHistoricalPriceUpdates();
    }

    /**
     * 
     *
     * @param Product|null $
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
    private function simulateHistoricalPriceUpdates()
    {

        $offsets = [30, 25, 20, 15, 10, 5, 2, 1];
        foreach ($offsets as $offset) {
            $fakeDate = Carbon::now()->subDays($offset);
            $this->weeklyPriceUpdate($fakeDate);
        }
    }

    /**
     * 
     *
     * @param Carbon 
     */ public function weeklyPriceUpdate(?Carbon $fakeDate = null)
    {
        if (is_null($fakeDate)) {
            $fakeDate = Carbon::now();
        }
        $faker = Faker::create();

        Product::chunk(100, function ($products) use ($faker, $fakeDate) {
            $data = [];
            $productIds = $products->pluck('id');

            $latestPrices = Price::whereIn('product_id', $productIds)
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('product_id');

            foreach ($products as $product) {
                if (isset($latestPrices[$product->id]) && $latestPrices[$product->id]->isNotEmpty()) {
                    $latestPrice = $latestPrices[$product->id]->first();

                    // Rustova hodnota
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
                        'created_at' => $fakeDate,
                        'updated_at' => $fakeDate,
                    ];
                }
            }
            if (!empty($data)) {
                DB::table('prices')->insert($data);
            }
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
        ];
    }
}
