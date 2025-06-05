<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VolunteeringInterestResource;
use App\Models\VolunteeringInterest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VolunteeringInterestController extends Controller
{
    /**
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        return VolunteeringInterestResource::collection(VolunteeringInterest::all());
    }

    /**
     * @return VolunteeringInterestResource
     */
    public function show(string $language, VolunteeringInterest $volunteeringInterest)
    {
        return VolunteeringInterestResource::make($volunteeringInterest);
    }
}
