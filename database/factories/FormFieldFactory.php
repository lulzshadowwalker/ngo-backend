<?php

namespace Database\Factories;

use App\Models\FormField;
use App\Models\ApplicationForm;
use App\Enums\FormFieldType;
use Illuminate\Database\Eloquent\Factories\Factory;

class FormFieldFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FormField::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'application_form_id' => ApplicationForm::factory(),
            'type' => fake()->randomElement(FormFieldType::cases()),
            'label' => [
                'en' => fake()->words(2, true),
                'ar' => 'تسمية الحقل'
            ],
            'placeholder' => [
                'en' => fake()->sentence(3),
                'ar' => 'نص تلميحي'
            ],
            'is_required' => fake()->boolean(70),
            'sort_order' => fake()->numberBetween(1, 10),
        ];
    }
}
