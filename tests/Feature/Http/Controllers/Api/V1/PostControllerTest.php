<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Http\Resources\V1\PostResource;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_posts()
    {
        $posts = Post::factory()->count(3)->create();
        $resource = PostResource::collection($posts);

        $response = $this->getJson(route('api.v1.posts.index'));

        $response->assertOk();
        $response->assertJson($resource->response()->getData(true));
    }

    public function test_it_shows_post()
    {
        $post = Post::factory()->create();
        $resource = PostResource::make($post);

        $response = $this->getJson(route('api.v1.posts.show', [
            'post' => $post->slug,
        ]));

        $response->assertOk();
        $response->assertJson($resource->response()->getData(true));
    }

    public function test_it_records_a_view_when_showing_post()
    {
        $post = Post::factory()->create();

        $this->assertEquals(0, views($post)->count());

        $this->getJson(route('api.v1.posts.show', [
            'post' => $post->slug,
        ]));

        $post->refresh();
        $this->assertEquals(1, views($post)->count());
    }
}
