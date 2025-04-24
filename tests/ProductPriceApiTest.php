<?php

namespace Tests\Feature;

use App\Models\Price;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPriceApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->product = Product::factory()->create();

        // Přidáme cenové záznamy
        Price::create([
            'product_id' => $this->product->id,
            'value' => 100,
            'retail' => 120,
            'wholesale' => 80,
            'condition' => 'New',
            'created_at' => Carbon::now()->subDays(30),
        ]);

        Price::create([
            'product_id' => $this->product->id,
            'value' => 120,
            'retail' => 140,
            'wholesale' => 100,
            'condition' => 'New',
            'created_at' => Carbon::now(),
        ]);
    }

    /** @test */
    public function it_can_get_price_history()
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/products/{$this->product->id}/price-history");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'history',
                'forecast',
                'current_price',
                'min_price',
                'max_price',
                'avg_price'
            ]);
    }

    /** @test */
    public function it_can_get_price_statistics()
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/products/{$this->product->id}/price-statistics");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'min',
                'max',
                'avg',
                'median',
                'count',
                'latest',
                'annual_growth'
            ]);
    }

    /** @test */
    public function it_can_get_growth_between_dates()
    {
        $fromDate = Carbon::now()->subDays(20)->format('Y-m-d');
        $toDate = Carbon::now()->format('Y-m-d');

        $response = $this->actingAs($this->user)
            ->getJson("/api/products/{$this->product->id}/growth?from_date={$fromDate}&to_date={$toDate}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'products',
                'total' => [
                    'initial_value',
                    'current_value',
                    'growth_percentage',
                    'growth_value'
                ]
            ]);
    }
}
