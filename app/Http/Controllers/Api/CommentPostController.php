<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostCommentRequest;
use App\Http\Resources\CommentResource;
use App\Http\Resources\PostResource;
use App\Models\Post;

//  TODO: Move requests and resources into v1/

class CommentPostController extends Controller
{
    public function index(Post $post)
    {
        return CommentResource::collection($post->comments);
    }

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
