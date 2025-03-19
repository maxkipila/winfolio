<?php

namespace Database\Factories;

use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['premium', 'standard', 'free']);

        return [
            'name' => match ($type) {
                'premium' => 'Premium',
                'standard' => 'Standard',
                'free' => 'Free',
            },
            'type'    => $type,

        ];
    }
}
