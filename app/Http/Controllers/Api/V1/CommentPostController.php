<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StorePostCommentRequest;
use App\Http\Resources\V1\CommentResource;
use App\Models\Post;

//  TODO: Move requests and resources into v1/

class CommentPostController extends Controller
{
    /**
     * List post comments
     *
     * Retrieve all comments for a specific post.
     *
     * @group Comments & Likes
     *
     * @unauthenticated
     *
     * @urlParam post string required The slug of the post. Example: environmental-conservation-initiative
     */
    public function index(Post $post)
    {
        return CommentResource::collection($post->comments);
    }

    /**
     * Create a new comment
     *
     * Add a new comment to a specific post. Authentication is required.
     *
     * @group Comments & Likes
     *
     * @authenticated
     *
     * @urlParam post string required The slug of the post to comment on. Example: environmental-conservation-initiative
     *
     * @bodyParam content string required The comment content. Example: This is a great initiative!
     */
    public function store(StorePostCommentRequest $request, Post $post)
    {
        $comment = $post->comments()->create([
            'user_id' => $request->user()->id,
            'content' => $request->content(),
        ]);

        return CommentResource::make($comment)
            ->response()
            ->setStatusCode(201);
    }
}
