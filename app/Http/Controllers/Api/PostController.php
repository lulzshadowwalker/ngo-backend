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
     * List all posts
     * 
     * Retrieve a paginated list of posts with optional filtering capabilities.
     * Posts can be filtered by various criteria such as organization, date, or status.
     *
     * @group Posts
     * @unauthenticated
     * 
     * @queryParam organization_id integer Filter posts by organization ID. Example: 1
     * @queryParam status string Filter posts by status (active, draft, archived). Example: active
     * @queryParam search string Search posts by title or content. Example: environment
     * @queryParam include string Include related data (comma-separated: likes,comments,organization). Example: likes,comments
     * 
     * @return AnonymousResourceCollection
     */
    public function index(PostFilter $filters)
    {
        return PostResource::collection(Post::filter($filters)->get());
    }

    /**
     * Get post details
     * 
     * Retrieve detailed information about a specific post using its slug.
     * You can include related data such as likes, comments, and organization details
     * using the include parameter.
     *
     * @group Posts
     * @unauthenticated
     * 
     * @urlParam post string required The slug of the post. Example: environmental-conservation-initiative
     * @queryParam include string Include related data (comma-separated: likes,comments,organization). Example: likes,comments,organization
     * 
     * @return PostResource
     */
    public function show(Post $post)
    {
        views($post)->record();

        $includes = ["likes", "comments", "organization"];
        foreach ($includes as $include) {
            if ($this->include($include)) {
                $post->load($include);
            }
        }

        return PostResource::make($post);
    }
}
