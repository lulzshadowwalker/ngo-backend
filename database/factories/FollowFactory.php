<?php

namespace Database\Factories;

use App\Models\Follow;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class FollowFactory extends Factory
{
    protected $model = Follow::class;

    public function definition(): array
    {
        $followableTypes = [
            Organization::class,
        ];

        $followableType = $this->faker->randomElement($followableTypes);

        return [
            'user_id' => User::factory(),
            'followable_id' => function () use ($followableType) {
                return $followableType::factory();
            },
            'followable_type' => $followableType,
        ];
    }
}
