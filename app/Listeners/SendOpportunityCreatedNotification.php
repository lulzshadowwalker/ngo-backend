<?php

namespace App\Listeners;

use App\Events\OpportunityCreated;
use App\Notifications\OpportunityCreatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendOpportunityCreatedNotification
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
    public function handle(OpportunityCreated $event): void
    {
        $organization = $event->opportunity->organization;
        $followers = $organization->follows;

        foreach ($followers as $follow) {
            $user = $follow->user;
            $user->notify(new OpportunityCreatedNotification($event->opportunity));
        }
    }
}
