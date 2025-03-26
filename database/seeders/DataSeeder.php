<?php

namespace Database\Seeders;

use App\Models\Price;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::factory(50)->create();

        $sets = Product::where('product_type', 'set')->orderBy('id')->limit(50)->get();
        $minifigs = Product::where('product_type', 'minifig')->orderBy('id')->limit(50)->get();

        $sets->each(function ($set) {
            Price::factory()->create([
                'product_id' => $set->id,
            ]);
        });
        $minifigs->each(function ($minifig) {
            Price::factory()->create([
                'product_id' => $minifig->id,
            ]);
        });
        $users->each(function ($user) use ($sets, $minifigs) {
            if ($sets->isNotEmpty()) {
                $randomSet = $sets->random();
                $user->products()->attach($randomSet->id);
            }

            if ($minifigs->isNotEmpty()) {
                $randomFig = $minifigs->random();
                $user->products()->attach($randomFig->id);
            }

            Subscription::factory()->create([
                'user_id' => $user->id,
            ]);
        });
    }
}
