<?php

namespace App\Observers;

use App\Events\ProgramCreated;
use App\Models\Program;

class ProgramObserver
{
    public function created(Program $programProgram)
    {
        event(new ProgramCreated($programProgram));
    }
}
