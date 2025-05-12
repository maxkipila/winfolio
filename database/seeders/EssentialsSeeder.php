<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Admin;
use App\Models\Theme;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class EssentialsSeeder extends Seeder
{
    /**
     * Seed základních dat pro produkční prostředí.
     * Vytvoří administrátora, nastaví základní kategorie a další nezbytná data.
     */
    public function run(): void
    {

        if (!Admin::where('email', 'admin@admin.com')->exists()) {
            Admin::create([
                'first_name' => 'Admin',
                'last_name' => 'Admin',
                'email' => 'admin@admin.com',
                'password' => '$2y$12$om7tnIs/OfsjdMBln3bVwec/4HOHEC159cceb1mP572BtEdXjeLKe',
            ]);

            $this->command->info('Admin uživatel vytvořen: admin@admin.com');
        } else {
            $this->command->info('Admin uživatel již existuje');
        }


        // Vytvoření testovacího uživatele pro produkci (volitelné)
        if (!User::where('email', 'tester@tester.com')->exists()) {
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

            $this->command->info('Testovací uživatel vytvořen: tester@tester.com');
        }
    }
}
