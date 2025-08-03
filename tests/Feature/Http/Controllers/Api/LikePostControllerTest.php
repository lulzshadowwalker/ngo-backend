<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikePostControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_likes_a_post(): void
    {
        // Arrange
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $this->actingAs($user);

        // Act
        $response = $this->postJson(route('api.v1.posts.likes.store', ['post' => $post->slug]));

        // Assert
        $response->assertNoContent(204);

        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'likeable_id' => $post->id,
            'likeable_type' => Post::class,
        ]);
    }

    public function test_it_unlikes_a_post(): void
    {
        // Arrange
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $post->likes()->create(['user_id' => $user->id]);
        $this->actingAs($user);

        // Act
        $response = $this->deleteJson(route('api.v1.posts.likes.destroy', ['post' => $post->slug]));

        // Assert
        $response->assertNoContent(204);

        $this->assertDatabaseMissing('likes', [
            'user_id' => $user->id,
            'likeable_id' => $post->id,
            'likeable_type' => Post::class,
        ]);
    }
}
