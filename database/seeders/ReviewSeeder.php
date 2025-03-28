<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use App\Models\Review;
use Faker\Factory as Faker;

class ReviewSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        $userIds = User::pluck('id')->all();
        $productIds = Product::pluck('id')->all();

        for ($i = 0; $i < 100; $i++) {
            Review::create([
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
            ]);
        }
    }
}
