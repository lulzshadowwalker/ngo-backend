<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Http\Resources\V1\CommentResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentPostControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_comments_on_a_post()
    {
        // Arrange
        $this->withoutExceptionHandling();

        $post = \App\Models\Post::factory()->create();
        $user = \App\Models\User::factory()->create();
        $comment = \App\Models\Comment::factory()->create([
            'user_id' => $user->id,
            'commentable_id' => $post->id,
            'commentable_type' => \App\Models\Post::class,
        ]);
        $resource = CommentResource::collection($post->comments);

        // Act
        $response = $this->actingAs($user, 'sanctum')->getJson(route('api.v1.posts.comments.index', ['post' => $post->slug]));

        // Assert
        $response->assertOk();

        $response->assertJson($resource->response()->getData(true));
    }

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
