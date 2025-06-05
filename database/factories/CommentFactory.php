<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\User;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition()
    {
        return [
            "user_id" => User::factory(),
            "content" => $this->faker->sentence,
            "commentable_id" => function () {
                return Post::factory()->create()->id;
            },
            "commentable_type" => Post::class,
        ];
    }

    public function forCommentable($commentable)
    {
        return $this->state(function (array $attributes) use ($commentable) {
            return [
                "commentable_id" => $commentable->id,
                "commentable_type" => get_class($commentable),
            ];
        });
    }
}
