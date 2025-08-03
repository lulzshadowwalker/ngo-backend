<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;

class LikePostController extends Controller
{
    public function store(Post $post)
    {
        $post->likes()->updateOrCreate(
            ['user_id' => auth()->user()->id],
            ['likeable_id' => $post->id, 'likeable_type' => Post::class]
        );

        return response()->noContent(204);
    }

    public function destroy(Post $post)
    {
        $post->likes()->where('user_id', auth()->user()->id)->delete();

        return response()->noContent(204);
    }
}
