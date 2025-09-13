<?php

namespace App\Listeners;

use App\Events\ProgramCreated;
use App\Notifications\ProgramCreatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendProgramCreatedNotification
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
    public function handle(ProgramCreated $event): void
    {
        $organization = $event->program->organization;
        $followers = $organization->follows;

        foreach ($followers as $follow) {
            $user = $follow->user;
            $user->notify(new ProgramCreatedNotification($event->program));
        }
    }
}
