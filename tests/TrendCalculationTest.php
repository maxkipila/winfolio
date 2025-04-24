<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Price;
use App\Models\Trend;
use App\Models\User;
use App\Services\TrendService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrendCalculationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function trending_products_are_calculated_correctly()
    {
        // 1. Vytvoření 10 produktů
        $products = Product::factory()->count(10)->create();

        // 2. Nastavení cen
        foreach ($products as $product) {
            Price::create([
                'product_id' => $product->id,
                'retail' => 100,
                'value' => 100 + ($product->id * 10), // Různé ceny
                'condition' => 'New',
                'created_at' => now(),
            ]);
        }

        // 3. Přidání produktů do portfolia uživatelů (vytvoření trending produktů)
        $users = User::factory()->count(5)->create();

        // První 3 produkty budou populární
        foreach ($users as $user) {
            $user->products()->attach([
                $products[0]->id,
                $products[1]->id,
                $products[2]->id,
            ]);
        }

        // 4. Testování výpočtu
        $trendService = new TrendService();
        $trendingProducts = $trendService->calculateTrendingProducts(5);

        // 5. Ověření výsledku
        $this->assertCount(3, $trendingProducts); // Máme jen 3 produkty, které byly přidány do portfolia
        $this->assertEquals($products[0]->id, $trendingProducts[0]->product_id);
        $this->assertEquals($products[1]->id, $trendingProducts[1]->product_id);
        $this->assertEquals($products[2]->id, $trendingProducts[2]->product_id);
    }

    // Podobné testy pro top movers a další funkcionalitu...
}
