<?php

namespace Database\Factories;

use App\Models\Award;
use Illuminate\Database\Eloquent\Factories\Factory;

class AwardFactory extends Factory
{
    protected $model = Award::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'type' => $this->faker->randomElement(['collection', 'achievement']),
            'category' => $this->faker->word,
            'description' => $this->faker->sentence,
            'icon' => $this->faker->imageUrl(100, 100, 'cats', true),
        ];
    }
}
