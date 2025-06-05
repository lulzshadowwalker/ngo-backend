<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Organization;
use App\Models\OrganizationPreference;

class OrganizationPreferenceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = OrganizationPreference::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'email_notifications' => fake()->boolean(),
            'push_notifications' => fake()->boolean(),
            'language' => fake()->languageCode(),
            'appearance' => fake()->randomElement(["light","dark","system"]),
            'organization_id' => Organization::factory(),
        ];
    }
}
