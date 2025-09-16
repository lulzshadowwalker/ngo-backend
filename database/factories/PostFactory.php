<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Post;
use App\Models\Sector;

class PostFactory extends BaseFactory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Post::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'title' => $this->localized(fn () => fake()->sentence(4)),
            'content' => $this->localized(fn () => fake()->paragraphs(3, true)),
            'organization_id' => Organization::factory(),
            'sector_id' => Sector::factory(),
        ];
    }
}
