<?php

namespace Database\Factories;

use App\Enums\ProfileVisibility;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Individual;
use App\Models\IndividualPreference;

class IndividualPreferenceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = IndividualPreference::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'profile_visibility' => fake()->randomElement(ProfileVisibility::values()),
            'individual_id' => Individual::factory(),
        ];
    }
}
