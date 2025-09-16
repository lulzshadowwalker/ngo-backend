<?php

namespace App\Observers;

use App\Enums\ApplicationStatus;
use App\Events\ApplicationStatusChanged;
use App\Models\Application;

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
