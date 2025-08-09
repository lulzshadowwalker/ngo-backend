<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Individual;
use App\Models\Location;
use App\Models\User;

class IndividualFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Individual::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'bio' => fake()->optional()->sentence(),
            'birthdate' => fake()->optional()->date(),
            'location_id' => Location::factory(),
        ];
    }
}
