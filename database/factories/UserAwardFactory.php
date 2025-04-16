<?php

namespace Database\Factories;

use App\Models\Award;
use App\Models\User;
use App\Models\UserAward;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserAwardFactory extends Factory
{
    protected $model = UserAward::class;

    public function definition()
    {
        return [
            'user_id'        => User::factory(),
            'award_id'       => Award::factory(),
            'earned_at'      => now(),
            'claimed_at'     => null,
            'notified'       => false,
            'user_description' => $this->faker->sentence,
            'count'          => $this->faker->numberBetween(1, 10),
            'value'          => $this->faker->randomFloat(2, 0, 1000),
            'percentage'     => $this->faker->randomFloat(2, 0, 100),
        ];
    }
}
