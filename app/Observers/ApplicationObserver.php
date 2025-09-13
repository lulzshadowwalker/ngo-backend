<?php

namespace App\Observers;

use App\Models\Application;
use App\Events\ApplicationStatusChanged;
use App\Enums\ApplicationStatus;

class ApplicationObserver
{
    public function updated(Application $application): void
    {
        if ($application->wasChanged('status')) {
            $previousStatus = $application->getOriginal('status');
            $newStatus = $application->status;

            if (
                $previousStatus === ApplicationStatus::Pending &&
                ($newStatus === ApplicationStatus::Approved || $newStatus === ApplicationStatus::Rejected)
            ) {
                event(new ApplicationStatusChanged($application, $previousStatus));
            }
        }
    }
}
