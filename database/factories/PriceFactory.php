<?php

namespace Database\Factories;

use App\Models\Price;
use Illuminate\Database\Eloquent\Factories\Factory;


class PriceFactory extends Factory
{
    protected $model = Price::class;

    public function definition(): array
    {
        return [
            'retail' => $this->faker->randomFloat(2, 5, 300),
            'value'  => $this->faker->randomFloat(2, 5, 500),
            'stav'   => $this->faker->randomElement(['Dobrý', 'Průměrný', 'Nový']),
        ];
    }
}
