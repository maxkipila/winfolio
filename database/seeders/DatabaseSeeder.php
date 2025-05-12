<?php

namespace Database\Seeders;

use App\Models\Set;
use App\Models\User;
use App\Models\Admin;
use App\Models\Price;
use App\Models\Minifig;
use Illuminate\Database\Seeder;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Database\Seeders\DataSeeder;
use Database\Seeders\NewsSeeder;
use Database\Seeders\PriceSeeder;
use Database\Seeders\ReviewSeeder;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\SeedUserProductHistory;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        User::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'tester@tester.com',
            'nickname' => 'TestUser',
            'prefix' => '+420',
            'phone' => '000000000',
            'day' => '01',
            'month' => '06',
            'year' => '2000',
            'street' => 'Ulice',
            'street_2' => 'Ulice 2',
            'psc' => '831 03',
            'city' => 'Město',
            'country' => 'CZ',
            'password' => '$2y$12$om7tnIs/OfsjdMBln3bVwec/4HOHEC159cceb1mP572BtEdXjeLKe',
            'is_admin' => 0,
        ]);


        Admin::create([
            'first_name' => 'Admin',
            'last_name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => '$2y$12$om7tnIs/OfsjdMBln3bVwec/4HOHEC159cceb1mP572BtEdXjeLKe',

        ]);

        Artisan::call('import:lego-data');

        $this->call(DataSeeder::class);
        $this->call(PriceSeeder::class);
        $this->call(ReviewSeeder::class);
        $this->call(NewsSeeder::class);

        // Agregace cen
        Artisan::call('prices:aggregate');

        // Aktualizace historických cen
        Artisan::call('prices:update-historical');

        Artisan::call('import:lego-images');

        // Přiřazení témat k minifigurkám
        Artisan::call('app:assign-themes-to-minifigs');


        /*    

        $this->call(DataSeeder::class);

        $this->call(PriceSeeder::class);

        $this->call(ReviewSeeder::class);

        gc_collect_cycles();

        $this->call(NewsSeeder::class);

        // Agregace cen
        Artisan::call('prices:aggregate');

        // Aktualizace historických cen
        Artisan::call('prices:update-historical');

        Artisan::call('import:lego-images');

        // Přiřazení témat k minifigurkám
        Artisan::call('app:assign-themes-to-minifigs'); */
    }
}
