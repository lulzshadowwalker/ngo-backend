<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\SkillResource;
use App\Models\Skill;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SkillController extends Controller
{
    /**
     * List all skills
     *
     * Retrieve a list of all available skills in the system.
     * Skills are used to categorize user expertise and organization needs.
     *
     * @group Skills & Locations
     *
     * @unauthenticated
     *
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        return SkillResource::collection(Skill::all());
    }

    /**
     * Get skill details
     *
     * Retrieve detailed information about a specific skill,
     * including its description and related information.
     *
     * @group Skills & Locations
     *
     * @unauthenticated
     *
     * @urlParam skill integer required The ID of the skill. Example: 1
     *
     * @return SkillResource
     */
    public function show(Skill $skill)
    {
        return SkillResource::make($skill);
    }
}
