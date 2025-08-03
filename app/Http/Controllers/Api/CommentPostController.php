<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostCommentRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;

//  TODO: Move requests and resources into v1/

class CommentPostController extends Controller
{
    public function store(StorePostCommentRequest $request, Post $post)
    {
        $post->comments()->create([
            'user_id' => $request->user()->id,
            'content' => $request->content(),
        ]);

        return PostResource::make($post->fresh()->load('comments'))
            ->response()
            ->setStatusCode(201);
    }
}
