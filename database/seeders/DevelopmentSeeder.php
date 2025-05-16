<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Admin;
use App\Models\Theme;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DevelopmentSeeder extends Seeder
{

    public function run(): void
    {

        Admin::updateOrCreate([
            'email' => 'admin@admin.com',

        ], [
            'first_name' => 'Admin',
            'last_name' => 'Admin',
            'password' => '$2y$12$om7tnIs/OfsjdMBln3bVwec/4HOHEC159cceb1mP572BtEdXjeLKe',
        ]);

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
        }
        Artisan::call('import:lego-data');
        Artisan::call('app:assign-themes-to-minifigs');
        Artisan::call('db:seed', ['--class' => 'PriceSeeder']);
    }
}
