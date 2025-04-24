<?php

namespace Tests\Unit;

use App\Models\Price;
use App\Models\Product;
use App\Models\User;
use App\Services\TrendService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrendServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TrendService $trendService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->trendService = new TrendService();
    }

    /** @test */
    public function it_calculates_median_price_correctly()
    {
        // Vytvoření testovacího produktu
        $product = Product::factory()->create([
            'product_num' => 'test-123',
            'product_type' => 'set',
            'name' => 'Test Set'
        ]);

        // Vytvoření různých cen
        $this->createPrices($product->id, [100, 150, 200, 250, 300]);

        // Test mediánové ceny
        $median = $this->trendService->getMedianPriceForProduct($product->id);
        $this->assertEquals(200, $median);
    }

    /** @test */
    public function it_handles_empty_prices_correctly()
    {
        $product = Product::factory()->create();

        $median = $this->trendService->getMedianPriceForProduct($product->id);
        $this->assertNull($median);

        $growth = $this->trendService->calculateGrowthForProductOptimized($product->id, 7);
        $this->assertNull($growth);
    }

    /** @test */
    public function it_calculates_growth_correctly()
    {
        $product = Product::factory()->create();

        // Vytvoření cen - před týdnem a nyní
        Price::create([
            'product_id' => $product->id,
            'retail' => 100,
            'value' => 100,
            'condition' => 'New',
            'created_at' => Carbon::now()->subDays(7)
        ]);

        Price::create([
            'product_id' => $product->id,
            'retail' => 150,
            'value' => 150,
            'condition' => 'New',
            'created_at' => Carbon::now()
        ]);

        $growth = $this->trendService->calculateGrowthForProductOptimized($product->id, 7);
        $this->assertEquals(50.0, $growth);
    }

    private function createPrices(int $productId, array $values, $startDate = null)
    {
        $date = $startDate ?: Carbon::now()->subDays(count($values));

        foreach ($values as $value) {
            Price::create([
                'product_id' => $productId,
                'retail' => $value,
                'value' => $value,
                'condition' => 'New',
                'created_at' => $date,
            ]);

            $date->addDay();
        }
    }
}
