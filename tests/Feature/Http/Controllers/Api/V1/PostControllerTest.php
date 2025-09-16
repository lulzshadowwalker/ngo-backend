<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_posts()
    {
        Post::factory()->count(3)->create();

        $response = $this->getJson(route('api.v1.posts.index'));

        $response->assertOk();
    }

    public function test_it_shows_post()
    {
        $post = Post::factory()->create();

        $response = $this->getJson(route('api.v1.posts.show', [
            'post' => $post->slug,
        ]));

        $response->assertOk();
    }
}
