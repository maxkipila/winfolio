<?php

namespace Database\Seeders;

use App\Models\Minifig;
use App\Models\Price;
use App\Models\Set;
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

        $sets = Set::orderBy('id')->limit(50)->get();
        $minifigs = Minifig::orderBy('id')->limit(50)->get();

        $sets->each(function ($set) {
            Price::factory()->create([
                'set_id'     => $set->id,
                'minifig_id' => null,
            ]);
        });

        $minifigs->each(function ($minifig) {
            Price::factory()->create([
                'set_id'     => null,
                'minifig_id' => $minifig->id,
            ]);
        });

        $users->each(function ($user) use ($sets, $minifigs) {
            if ($sets->isNotEmpty()) {
                $randomSet = $sets->random();
                $user->sets()->attach($randomSet->id);
            }

            if ($minifigs->isNotEmpty()) {
                $randomFig = $minifigs->random();
                $user->minifigs()->attach($randomFig->id);
            }

            Subscription::factory()->create([
                'user_id' => $user->id,
            ]);
        });
    }
}
