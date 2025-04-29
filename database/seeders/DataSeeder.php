<?php

namespace Database\Seeders;

use App\Models\Price;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;

class DataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Omezení počtu vytvářených uživatelů
        $userCount = 50; // Místo 50
        $this->command->info("Vytvářím {$userCount} uživatelů...");
        $users = User::factory($userCount)->create();

        // Omezení počtu produktů pro přiřazení
        $setLimit = 50; // Místo 50
        $minifigLimit = 50; // Místo 50

        $this->command->info("Načítám {$setLimit} setů a {$minifigLimit} minifigurek...");
        $sets = Product::where('product_type', 'set')->orderBy('id')->limit($setLimit)->get();
        $minifigs = Product::where('product_type', 'minifig')->orderBy('id')->limit($minifigLimit)->get();

        // Přeskočíme seedování PriceSeeder zde, protože to děláme samostatně
        $this->command->info("Přiřazuji produkty uživatelům...");

        // Zpracování po menších dávkách pro snížení využití paměti
        $users->chunk(5)->each(function ($userChunk) use ($sets, $minifigs) {
            foreach ($userChunk as $user) {
                // Přiřadíme pouze 1-2 produkty každému uživateli
                if ($sets->isNotEmpty()) {
                    $randomSet = $sets->random();
                    $user->products()->attach($randomSet->id);
                }

                // 50% šance na přiřazení minifigurky
                if ($minifigs->isNotEmpty() && rand(0, 100) < 50) {
                    $randomFig = $minifigs->random();
                    $user->products()->attach($randomFig->id);
                }

                // Vytvoření základního předplatného
                Subscription::factory()->create([
                    'user_id' => $user->id,
                ]);
            }

            // Vynucené uvolnění paměti
            gc_collect_cycles();
        });

        $this->command->info("Uživatelé, produkty a předplatná úspěšně vytvořeny.");
    }
}
