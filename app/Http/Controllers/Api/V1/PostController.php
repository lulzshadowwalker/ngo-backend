<?php

namespace App\Http\Controllers\Api\V1;

use App\Filters\PostFilter;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\V1\SearchPostRequest;
use App\Http\Resources\V1\PostResource;
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
        $includes = ["likes", "comments", "organization", "sector"];
        foreach ($includes as $include) {
            if ($this->include($include)) {
                $post->load($include);
            }
        }

        return PostResource::make($post);
    }

    /**
     * Search Posts
     *
     * Search for posts based on a query string. This endpoint allows users
     * to find posts by title or content, facilitating easier discovery of relevant information.
     *
     * @group Posts
     * @unauthenticated
     *
     * @queryParam query string required The search query string. Example: health
     *
     * @return AnonymousResourceCollection
     */
    public function search(SearchPostRequest $request)
    {
        $query = $request->input('query', '') ?? '';

        $query = Post::search($query);

        $query->when($request->has('sector'), function ($q) use ($request) {
            $q->where('sector_id', (int) $request->input('sector'));
        });

        $posts = $query->get();

        $posts->load(['organization', 'sector', 'likes', 'comments']);

        return PostResource::collection($posts);
    }
}
