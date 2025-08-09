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
     * @response 200 scenario="Success" {
     *   "data": [
     *     {
     *       "id": 1,
     *       "title": "Environmental Conservation Initiative",
     *       "slug": "environmental-conservation-initiative",
     *       "content": "Join us in our mission to protect the environment...",
     *       "excerpt": "Join us in our mission to protect...",
     *       "published_at": "2024-01-15T10:00:00.000000Z",
     *       "created_at": "2024-01-15T10:00:00.000000Z",
     *       "updated_at": "2024-01-15T10:00:00.000000Z"
     *     }
     *   ]
     * }
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
     * @response 200 scenario="Success" {
     *   "data": {
     *     "id": 1,
     *     "title": "Environmental Conservation Initiative",
     *     "slug": "environmental-conservation-initiative",
     *     "content": "Join us in our mission to protect the environment and create a sustainable future for generations to come...",
     *     "excerpt": "Join us in our mission to protect the environment...",
     *     "published_at": "2024-01-15T10:00:00.000000Z",
     *     "organization": {
     *       "id": 1,
     *       "name": "Green Earth Foundation",
     *       "slug": "green-earth-foundation"
     *     },
     *     "likes_count": 15,
     *     "comments_count": 3,
     *     "created_at": "2024-01-15T10:00:00.000000Z",
     *     "updated_at": "2024-01-15T10:00:00.000000Z"
     *   }
     * }
     * 
     * @response 404 scenario="Post not found" {
     *   "message": "Post not found"
     * }
     * 
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
