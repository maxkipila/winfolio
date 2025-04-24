<?php

namespace App\Console\Commands;

use App\Models\Price;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SeedUserProductHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:seed-user-product-history';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */

    public function handle()
    {
        $user = User::first();
        $products = $user->products;

        foreach ($products as $product) {
            $exists = Price::where('product_id', $product->id)
                ->where('created_at', '<=', Carbon::now()->subYear())
                ->exists();

            if (!$exists) {
                Price::create([
                    'product_id' => $product->id,
                    'retail' => 100,
                    'wholesale' => 60,
                    'value' => 80,
                    'condition' => 'New',
                    'type' => 'market',
                    'created_at' => Carbon::now()->subYear()->subDay()->startOfDay(),
                    'updated_at' => Carbon::now()->subYear()->startOfDay(),
                ]);
            }
        }

        $this->info('✅ Seeded 1Y old price for user products.');
    }
}
