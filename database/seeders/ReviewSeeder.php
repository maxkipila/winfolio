<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use App\Models\Review;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class ReviewSeeder extends Seeder
{
    public function run()
    {
        // Zvýšení limitu paměti na 2GB (ale i tak budeme optimalizovat)
        ini_set('memory_limit', '2G');

        // Vypnutí query logu pro úsporu paměti
        DB::disableQueryLog();

        $faker = Faker::create();

        // Načteme ID efektivněji - bez načítání celých modelů
        $userIds = User::pluck('id')->toArray();
        $productIds = Product::pluck('id')->toArray();

        // Celkový počet recenzí, které chceme vytvořit
        $totalReviews = 100;

        // Vytváříme recenze po menších dávkách
        $batchSize = 20;
        $batches = ceil($totalReviews / $batchSize);

        $this->command->info("Creating {$totalReviews} reviews in {$batches} batches");

        for ($batch = 0; $batch < $batches; $batch++) {
            $reviewsData = [];

            // Počet recenzí v aktuální dávce
            $reviewsInBatch = min($batchSize, $totalReviews - ($batch * $batchSize));

            for ($i = 0; $i < $reviewsInBatch; $i++) {
                $reviewsData[] = [
                    'user_id'     => $faker->randomElement($userIds),
                    'product_id'  => $faker->randomElement($productIds),
                    'rating'      => $faker->numberBetween(1, 5),
                    'comment'     => $faker->sentence(10),
                    'role'        => $faker->randomElement(['collector', 'investor', 'both']),
                    'invested_in' => $faker->randomElement([
                        null,
                        '2 sets',
                        '50 USD',
                        '3 minifigs',
                        'N/A',
                        '1500 CZK'
                    ]),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }

            // Hromadné vložení
            Review::insert($reviewsData);

            // Informace o postupu
            $this->command->info("Batch " . ($batch + 1) . "/" . $batches . " completed");

            // Vyčištění paměti
            unset($reviewsData);
            gc_collect_cycles();
        }

        $this->command->info("Reviews seeding completed");
    }
}
