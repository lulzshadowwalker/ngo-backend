<?php

namespace Database\Factories;

use App\Models\Skill;

class SkillFactory extends BaseFactory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Skill::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            "name" => $this->localized(fn() => fake()->name()),
        ];
    }
}
