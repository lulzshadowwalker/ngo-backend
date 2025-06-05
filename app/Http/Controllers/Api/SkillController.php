<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SkillResource;
use App\Models\Skill;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SkillController extends Controller
{
    /**
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        return SkillResource::collection(Skill::all());
    }

    /**
     * @return SkillResource
     */
    public function show(string $language, Skill $skill)
    {
        return SkillResource::make($skill);
    }
}
