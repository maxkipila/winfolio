<?php

namespace Database\Factories;

use App\Models\Theme;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $types = ['set', 'minifig'];
        $availabilityOptions = ['Retail', 'Retired', 'Retiring soon', 'Unavailable', 'Coming soon'];

        return [
            'product_num' => $this->faker->unique()->bothify('####?'),
            'product_type' => $this->faker->randomElement($types),
            'name' => $this->faker->words(3, true),
            'year' => $this->faker->numberBetween(1999, 2024),
            'theme_id' => Theme::inRandomOrder()->first()?->id ?? null,
            'num_parts' => $this->faker->numberBetween(1, 1000),
            'img_url' => $this->faker->imageUrl(),
            'availability' => $this->faker->randomElement($availabilityOptions),
        ];
    }
}
