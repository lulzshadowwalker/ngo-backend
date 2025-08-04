<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentPostControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_stores_a_comment_on_a_post()
    {
        // Arrange
        $this->withoutExceptionHandling();

        $post = \App\Models\Post::factory()->create();
        $user = \App\Models\User::factory()->create();

        // Act
        $response = $this->actingAs($user, 'sanctum')->postJson(route('api.v1.posts.comments.store', ['post' => $post->slug]), [
            'data' => [
                'relationships' => [
                    'comments' => [
                        'data' => [
                            'attributes' => [
                                'content' => 'This is a comment.',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertCreated();

        // Assert
        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'content' => 'This is a comment.',
            'commentable_id' => $post->id,
            'commentable_type' => \App\Models\Post::class,
        ]);
    }
}
