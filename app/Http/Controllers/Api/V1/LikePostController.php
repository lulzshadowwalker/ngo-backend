<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Post;

class LikePostController extends Controller
{
    /**
     * Like a post
     * 
     * Add a like to a specific post. If the user has already liked the post,
     * this endpoint will update the existing like record. Authentication is required.
     *
     * @group Comments & Likes
     * @authenticated
     * 
     * @urlParam post string required The slug of the post to like. Example: environmental-conservation-initiative
     */
    public function store(Post $post)
    {
        $post->likes()->updateOrCreate(
            ['user_id' => auth()->user()->id],
            ['likeable_id' => $post->id, 'likeable_type' => Post::class]
        );

        return response()->noContent(204);
    }

    /**
     * Unlike a post
     * 
     * Remove a like from a specific post. If the user hasn't liked the post,
     * this endpoint will have no effect. Authentication is required.
     *
     * @group Comments & Likes
     * @authenticated
     * 
     * @urlParam post string required The slug of the post to unlike. Example: environmental-conservation-initiative
     */
    public function destroy(Post $post)
    {
        $post->likes()->where('user_id', auth()->user()->id)->delete();

        return response()->noContent(204);
    }
}
