<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Price;
use Illuminate\Database\Eloquent\Factories\Factory;

class PriceFactory extends Factory
{
    protected $model = Price::class;

    public function definition(): array
    {
        $product = Product::inRandomOrder()->first() ?? Product::factory()->create();

        // Generate base price based on product type
        $basePrice = match ($product->product_type) {
            'set' => $this->calculateSetPrice($product),
            'minifig' => $this->faker->randomFloat(2, 1, 50),
            default => $this->faker->randomFloat(2, 10, 200)
        };

        return [
            'product_id' => $product->id,
            'retail' => round($basePrice * 1.3, 2),
            'wholesale' => round($basePrice * 0.7, 2),
            'value' => round($basePrice, 2),
            /*             'growth' => $this->faker->randomFloat(2, -10, 20),
            'annual' => $this->faker->randomFloat(2, -5, 15), */
            'condition' => $this->generateCondition($product->product_type)
        ];
    }

    /**
     * Calculate price for a set based on number of parts
     */
    private function calculateSetPrice(Product $product): float
    {
        // If no parts, use random pricing
        if (!$product->num_parts) {
            return $this->faker->randomFloat(2, 20, 500);
        }

        // Base price calculation: parts * 0.5, with min and max bounds
        return min(max($product->num_parts * 0.5, 10), 1000);
    }

    /**
     * Generate condition based on product type
     */
    private function generateCondition(string $productType): string
    {
        return match ($productType) {
            'set' => $this->faker->randomElement(['New', 'Used', 'Sealed']),
            'minifig' => $this->faker->randomElement(['Mint', 'Good', 'Played']),
            default => 'Unknown'
        };
    }

    /**
     * Create a specific price variant for testing
     */
    public function forProduct(Product $product)
    {
        return $this->state(fn(array $attributes) => [
            'product_id' => $product->id
        ]);
    }

    /**
     * Create a price with specific condition
     */
    public function withCondition(string $condition)
    {
        return $this->state(fn(array $attributes) => [
            'condition' => $condition
        ]);
    }
}
