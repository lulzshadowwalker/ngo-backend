<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\Organization;
use App\Models\Sector;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganizationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Organization::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'bio' => fake()->sentence(),
            'website' => fake()->url(),
            'sector_id' => Sector::factory(),
            'location_id' => Location::factory(),
            'contact_email' => fake()->optional()->safeEmail(),
        ];
    }
}
