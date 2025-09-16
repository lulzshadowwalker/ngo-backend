<?php

namespace Database\Factories;

use App\Enums\ProgramStatus;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Program>
 */
class ProgramFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => [
                'en' => $this->faker->sentence(),
                'ar' => 'برنامج '.$this->faker->words(3, true),
            ],
            'description' => [
                'en' => $this->faker->paragraph(),
                'ar' => $this->faker->paragraph().' باللغة العربية',
            ],
            'status' => $this->faker->randomElement(ProgramStatus::cases()),
            'organization_id' => Organization::factory(),
        ];
    }
}
