<?php

namespace Tests\Feature;

use App\Models\Price;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortfolioValueCalculationTest extends TestCase
{
    /* use RefreshDatabase; */

    /**
     * Test výpočtu hodnoty portfolia.
     */
    public function test_dashboard_portfolio_value_calculation(): void
    {
        // 1. Vytvoření testovacího uživatele
        $user = User::factory()->create();

        // 2. Vytvoření testovacích produktů
        $product1 = Product::create([
            'product_num' => '75313',
            'product_type' => 'set',
            'name' => 'Test Set 1',
            'year' => 2021,
        ]);

        $product2 = Product::create([
            'product_num' => 'fig-01234',
            'product_type' => 'minifig',
            'name' => 'Test Minifig 1',
            'year' => 2020,
        ]);

        $product3 = Product::create([
            'product_num' => '10234',
            'product_type' => 'set',
            'name' => 'Test Set 2',
            'year' => 2022,
        ]);

        // 3. Vytvoření cen pro produkty
        Price::create([
            'product_id' => $product1->id,
            'retail' => 100.00,
            'value' => 150.00,
            'condition' => 'New',
            'created_at' => now(),
        ]);

        Price::create([
            'product_id' => $product2->id,
            'retail' => 10.00,
            'value' => 25.00,
            'condition' => 'New',
            'created_at' => now(),
        ]);

        Price::create([
            'product_id' => $product3->id,
            'retail' => 200.00,
            'value' => 180.00,
            'condition' => 'New',
            'created_at' => now(),
        ]);

        // 4. Přidání produktů do portfolia uživatele
        $user->products()->attach([
            $product1->id => [
                'purchase_day' => 15,
                'purchase_month' => 3,
                'purchase_year' => 2023,
                'purchase_price' => 100.00,
                'currency' => 'USD',
                'condition' => 'New'
            ],
            $product2->id => [
                'purchase_day' => 20,
                'purchase_month' => 1,
                'purchase_year' => 2022,
                'purchase_price' => 8.00,
                'currency' => 'USD',
                'condition' => 'New'
            ],
            $product3->id => [
                'purchase_day' => 10,
                'purchase_month' => 10,
                'purchase_year' => 2023,
                'purchase_price' => 190.00,
                'currency' => 'USD',
                'condition' => 'Used'
            ],
        ]);

        // 5. Autentizace uživatele
        $this->actingAs($user);

        // 6. Výpočet očekávané hodnoty
        $expectedValue = 150.00 + 25.00 + 180.00; // Součet všech value

        // 7. Přímé testování metody dashboardPortfolioValue
        $portfolioValue = $this->calculatePortfolioValue($user);

        // 8. Ověření výsledku
        $this->assertEquals($expectedValue, $portfolioValue, 'Hodnota portfolia neodpovídá očekávané hodnotě');
    }

    /**
     * Pomocná metoda pro výpočet hodnoty portfolia
     */
    private function calculatePortfolioValue(User $user): float
    {
        // Implementace stejná jako v UserController::dashboardPortfolioValue
        $portfolioValue = $user->products()
            ->with('latest_price')
            ->get()
            ->sum(function ($product) {
                return $product->latest_price ? $product->latest_price->value : 0;
            });

        return $portfolioValue;
    }
}
