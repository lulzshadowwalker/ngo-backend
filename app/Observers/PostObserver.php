<?php

namespace App\Observers;

use App\Models\Post;
use Illuminate\Support\Str;

class PostObserver
{
    /**
     * Handle the Post "creating" event.
     *
     * @param  \App\Models\Post  $post
     * @return void
     */
    public function creating(Post $post)
    {
        if (empty($post->slug)) {
            $baseSlug = Str::slug($post->getTranslation('title', 'en'));
            $slug = $baseSlug;
            $i = 1;
            while (Post::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $i++;
            }
            $post->slug = $slug;
        }
    }
}
