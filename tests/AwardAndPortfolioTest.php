<?php

namespace Tests\Feature;

use App\Models\Award;
use App\Models\AwardCondition;
use App\Models\Price;
use App\Models\Product;
use App\Models\User;
use App\Services\AwardCheckerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AwardAndPortfolioTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_gets_award_for_specific_product()
    {
        // 1. Vytvoření odznaku za konkrétní produkt
        $product = Product::factory()->create();

        $award = Award::create([
            'name' => 'Test Award',
            'description' => 'Test award for owning a specific product',
            'type' => 'collection',
            'category' => 'test',
        ]);

        AwardCondition::create([
            'award_id' => $award->id,
            'condition_type' => 'specific_product',
            'product_id' => $product->id,
        ]);

        // 2. Vytvoření uživatele
        $user = User::factory()->create();

        // 3. Přidání produktu do portfolia uživatele
        $user->products()->attach($product->id, [
            'purchase_price' => 100,
            'purchase_day' => 1,
            'purchase_month' => 1,
            'purchase_year' => 2023,
        ]);

        // 4. Kontrola odznaků
        $awardChecker = new AwardCheckerService();
        $newAwards = $awardChecker->checkUserAwards($user);

        // 5. Ověření výsledku
        $this->assertCount(1, $newAwards);
        $this->assertEquals($award->id, $newAwards[0]->id);
    }

    /** @test */
    public function portfolio_value_is_calculated_correctly_with_multiple_price_history()
    {
        // 1. Vytvoření uživatele
        $user = User::factory()->create();

        // 2. Vytvoření produktů
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        // 3. Přidání cen produktů - několik cen pro každý produkt
        $this->createPriceHistory($product1->id, [100, 110, 120, 130, 140]);
        $this->createPriceHistory($product2->id, [50, 60, 70, 80, 90]);

        // 4. Přidání produktů do portfolia uživatele
        $user->products()->attach([
            $product1->id => ['purchase_price' => 90],
            $product2->id => ['purchase_price' => 40],
        ]);

        // 5. Výpočet hodnoty portfolia - měl by použít nejnovější ceny (140 + 90 = 230)
        $portfolioValue = $user->products()
            ->with('latest_price')
            ->get()
            ->sum(function ($product) {
                return $product->latest_price ? $product->latest_price->value : 0;
            });

        $this->assertEquals(230, $portfolioValue);
    }

    /**
     * Pomocná metoda pro vytvoření historie cen
     */
    private function createPriceHistory(int $productId, array $values)
    {
        $date = now()->subDays(count($values));

        foreach ($values as $value) {
            Price::create([
                'product_id' => $productId,
                'retail' => $value,
                'value' => $value,
                'condition' => 'New',
                'created_at' => $date->copy(),
            ]);

            $date->addDay();
        }
    }
}
