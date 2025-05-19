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
    }



    public function seedPrices(?Product $product = null)
    {
        DB::disableQueryLog();
        $faker = Faker::create();

        // posledni rok
        $startDate = now()->subYear();
        $currentDate = now();

        if ($product) {
            // Zpracování jednoho konkrétního produktu
            $this->seedPriceHistoryForProduct($product, $faker, $startDate, $currentDate);
        } else {
            // Zpracování všech produktů bez cen
            Product::whereDoesntHave('prices')
                ->chunkById(50, function ($products) use ($faker, $startDate, $currentDate) {
                    foreach ($products as $product) {
                        $this->seedPriceHistoryForProduct($product, $faker, $startDate, $currentDate);
                    }
                    gc_collect_cycles();
                });
        }
    }

    private function seedPriceHistoryForProduct(Product $product, $faker, $startDate, $currentDate)
    {
        /* if (Price::where('product_id', $product->id)->exists()) {
            return;
        } */
        // Základní cena podle typu produktu
        $basePrice = $product->product_type === 'set'
            ? ($product->num_parts ? min(max($product->num_parts * 0.5, 10), 1000) : $faker->randomFloat(2, 20, 500))
            : $faker->randomFloat(2, 1, 50);

        // Typ trendu ceny
        $trendType = $faker->randomElement(['rising', 'falling', 'volatile', 'stable']);

        $priceRecords = [];
        $date = $startDate->copy();
        $monthIndex = 0;

        // Generování dat po měsících
        while ($date->lt($currentDate)) {
            $month = $date->month;
            $year = $date->year;
            $isCurrentMonth = ($date->year === $currentDate->year && $date->month === $currentDate->month);


            if ($isCurrentMonth) {
                // Pro aktuální měsíc - každý den
                $days = range(1, min($currentDate->day, $date->daysInMonth));
            } else {
                // Pro starší měsíce - vybrané dny nebo žádné pro "díry"
                $days = $faker->boolean(80)
                    ? $faker->randomElements([1, 10, 15, 20, $date->daysInMonth], $faker->numberBetween(1, 3))
                    : [];
            }

            // Vytvoření cenových záznamů pro vybrané dny
            foreach ($days as $day) {
                $recordDate = Carbon::createFromDate($year, $month, $day)->startOfDay();

                // Trend + variace
                $trendFactor = $this->getTrendFactor($trendType, $monthIndex, 12, $faker);
                $dailyVariation = $faker->randomFloat(2, 0.9, 1.1);

                // Variacni faktor pro nastaveni velkych skoku
                if ($faker->boolean(10)) {
                    $dailyVariation *= $faker->randomElement([0.75, 1.25]);
                }

                $priceValue = round($basePrice * $trendFactor * $dailyVariation, 2);

                $priceRecords[] = [
                    'product_id' => $product->id,
                    'retail' => round($priceValue * 1.3, 2),
                    'value' => $priceValue,
                    'date' => $recordDate->toDateString(),
                    'currency' => 'USD',
                    'created_at' => $recordDate,
                    'updated_at' => $recordDate
                ];
            }

            $date->addMonth();
            $monthIndex++;
        }


        if (!empty($priceRecords)) {
            foreach (array_chunk($priceRecords, 50) as $chunk) {
                DB::table('prices')->insert($chunk);
            }
        }
    }



    private function getTrendFactor($trendType, $index, $totalMonths, $faker)
    {
        $position = $index / max(1, $totalMonths - 1); // 0 = nejnovější, 1 = nejstarší

        switch ($trendType) {
            case 'rising':
                // Rostoucí trend 
                return 1 - ($position * $faker->randomFloat(2, 0.2, 0.5));
            case 'falling':
                // Klesající trend
                return 1 + ($position * $faker->randomFloat(2, 0.1, 0.4));
            case 'volatile':
                // Volatilní trend 
                return 1 + (($faker->randomFloat(2, -0.2, 0.2)) - ($position * 0.1));
            case 'stable':
            default:
                // Stabilní trend 
                return 1 + $faker->randomFloat(2, -0.05, 0.05);
        }
    }
}
