<?php

namespace Database\Factories;

use App\Models\Location;

class LocationFactory extends BaseFactory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Location::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'city' => $this->localized(fn () => fake()->city()),
            'country' => $this->localized(fn () => fake()->country()),
        ];
    }
}
