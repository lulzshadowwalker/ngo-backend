<?php

namespace App\Listeners;

use App\Events\PostCreated;
use App\Notifications\PostCreatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendPostCreatedNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PostCreated $event): void
    {
        $organization = $event->post->organization;
        $followers = $organization->follows;

        foreach ($followers as $follow) {
            $user = $follow->user;
            $user->notify(new PostCreatedNotification($event->post));
        }
    }
}
