<?php

namespace Tests\Feature;

use App\Models\Price;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AggregateProductPricesCommandTest extends TestCase
{
    /*  use RefreshDatabase; */

    /** @test */
    public function it_aggregates_prices_for_a_product()
    {
        // Vytvoříme produkt
        $product = Product::factory()->create([
            'name' => 'Test LEGO Set',
            'product_num' => 'TEST-123',
            'product_type' => 'set'
        ]);

        // Vytvoříme cenové položky ve dvou měsících
        $month1 = Carbon::now()->subMonths(2)->startOfMonth();
        Price::create([
            'product_id' => $product->id,
            'value' => 100,
            'retail' => 120,
            'wholesale' => 80,
            'condition' => 'New',
            'created_at' => $month1->copy()->addDays(5)
        ]);

        Price::create([
            'product_id' => $product->id,
            'value' => 110,
            'retail' => 130,
            'wholesale' => 90,
            'condition' => 'New',
            'created_at' => $month1->copy()->addDays(15)
        ]);

        $month2 = Carbon::now()->subMonth()->startOfMonth();
        Price::create([
            'product_id' => $product->id,
            'value' => 115,
            'retail' => 135,
            'wholesale' => 95,
            'condition' => 'New',
            'created_at' => $month2->copy()->addDays(5)
        ]);

        Price::create([
            'product_id' => $product->id,
            'value' => 120,
            'retail' => 140,
            'wholesale' => 100,
            'condition' => 'New',
            'created_at' => $month2->copy()->addDays(15)
        ]);

        // Spustíme příkaz pro agregaci
        $this->artisan("app:aggregate-prices --product={$product->id}")
            ->assertExitCode(0);

        // Ověříme, že byla vytvořena agregovaná data
        $aggregated = Price::where('product_id', $product->id)
            ->where('type', 'aggregated')
            ->orderBy('created_at')
            ->get();

        // Měla by existovat dvě agregovaná data (jedno pro každý měsíc)
        $this->assertEquals(2, $aggregated->count());

        // Kontrola prvního měsíce - medián z 100 a 110 je 105
        $this->assertEquals(105, $aggregated[0]->value);

        // Kontrola druhého měsíce - medián z 115 a 120 je 117.5
        $this->assertEquals(117.5, $aggregated[1]->value);
    }
}
