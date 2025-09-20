<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DeactivateAccountController extends Controller
{
    public function store()
    {
        Auth::user()->deactivate();

        return response()->noContent();
    }
}
