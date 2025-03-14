<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@user.com',
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


        User::factory()->create([
            'first_name' => 'Admin',
            'last_name' => 'Admin',
            'email' => 'admin@admin.com',
            'nickname' => 'AdminUser',
            'prefix' => '+420',
            'phone' => '111111111',
            'day' => '01',
            'month' => '06',
            'year' => '2000',
            'street' => 'Ulice',
            'street_2' => 'Ulice 2',
            'psc' => '831 03',
            'city' => 'Město',
            'country' => 'CZ',
            'password' => '$2y$12$om7tnIs/OfsjdMBln3bVwec/4HOHEC159cceb1mP572BtEdXjeLKe',
            'is_admin' => 1,
        ]);
    }
}
