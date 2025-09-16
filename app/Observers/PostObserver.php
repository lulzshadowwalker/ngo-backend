<?php

namespace App\Observers;

use App\Events\PostCreated;
use App\Models\Post;
use Illuminate\Support\Str;

class PostObserver
{
    /**
     * Handle the Post "creating" event.
     *
     * @return void
     */
    public function creating(Post $post)
    {
        if (empty($post->slug)) {
            $baseSlug = Str::slug($post->getTranslation('title', 'en'));
            $slug = $baseSlug;
            $i = 1;
            while (Post::where('slug', $slug)->exists()) {
                $slug = $baseSlug.'-'.$i++;
            }
            $post->slug = $slug;
        }
    }

    public function created(Post $post)
    {
        event(new PostCreated($post));
    }
}
