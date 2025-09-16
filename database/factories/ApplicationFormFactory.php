<?php

namespace Database\Factories;

use App\Models\ApplicationForm;
use App\Models\Opportunity;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicationFormFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ApplicationForm::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'opportunity_id' => Opportunity::factory(),
            'organization_id' => Organization::factory(),
            'title' => [
                'en' => fake()->sentence(4),
                'ar' => 'نموذج طلب تطوع',
            ],
            'description' => [
                'en' => fake()->paragraph(),
                'ar' => 'وصف نموذج طلب التطوع',
            ],
            'is_active' => true,
        ];
    }
}
