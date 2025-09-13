<?php

namespace App\Observers;

use App\Events\OpportunityCreated;
use App\Models\Opportunity;

class OpportunityObserver
{
    public function created(Opportunity $opportunity)
    {
        event(new OpportunityCreated($opportunity));
    }
}
