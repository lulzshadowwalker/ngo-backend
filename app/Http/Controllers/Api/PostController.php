<?php

namespace App\Http\Controllers\Api;

use App\Filters\PostFilter;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PostController extends ApiController
{
    /**
     * @return AnonymousResourceCollection
     */
    public function index(PostFilter $filters)
    {
        return PostResource::collection(Post::filter($filters)->get());
    }

    /**
     * @return PostResource
     */
    public function show(Post $post)
    {
        $includes = ["likes", "comments", "organization"];
        foreach ($includes as $include) {
            if ($this->include($include)) {
                $post->load($include);
            }
        }

        return PostResource::make($post);
    }
}
