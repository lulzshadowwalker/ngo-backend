<?php

namespace App\Listeners;

use App\Events\ApplicationStatusChanged;
use App\Notifications\ApplicationStatusChangedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendApplicationStatusChangedNotification
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
    public function handle(ApplicationStatusChanged $event): void
    {
        $user = $event->application->user;
        $user->notify(new ApplicationStatusChangedNotification(
            $event->application,
            $event->previousStatus
        ));
    }
}
