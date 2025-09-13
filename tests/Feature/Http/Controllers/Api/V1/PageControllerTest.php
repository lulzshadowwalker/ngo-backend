<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Http\Resources\V1\PageResource;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_pages()
    {
        $pages = Page::factory()->count(3)->create();
        $resource = PageResource::collection($pages);

        $response = $this->getJson(route('api.v1.pages.index'));

        $response->assertOk();
        $response->assertJson($resource->response()->getData(true));
    }

    public function test_it_shows_page()
    {
        $page = Page::factory()->create();
        $resource = PageResource::make($page);

        $response = $this->getJson(route('api.v1.pages.show', [
            'page' => $page->slug,
        ]));

        $response->assertOk();
        $response->assertJson($resource->response()->getData(true));
    }
}
