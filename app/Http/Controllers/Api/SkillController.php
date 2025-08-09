<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SkillResource;
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
     * @unauthenticated
     * 
     * @response 200 scenario="Success" {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Environmental Science",
     *       "slug": "environmental-science",
     *       "description": "Knowledge and expertise in environmental conservation",
     *       "category": "Science",
     *       "created_at": "2024-01-15T10:00:00.000000Z",
     *       "updated_at": "2024-01-15T10:00:00.000000Z"
     *     },
     *     {
     *       "id": 2,
     *       "name": "Community Outreach",
     *       "slug": "community-outreach",
     *       "description": "Experience in community engagement and outreach programs",
     *       "category": "Social",
     *       "created_at": "2024-01-15T10:00:00.000000Z",
     *       "updated_at": "2024-01-15T10:00:00.000000Z"
     *     }
     *   ]
     * }
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
     * @unauthenticated
     * 
     * @urlParam skill integer required The ID of the skill. Example: 1
     * 
     * @response 200 scenario="Success" {
     *   "data": {
     *     "id": 1,
     *     "name": "Environmental Science",
     *     "slug": "environmental-science",
     *     "description": "Knowledge and expertise in environmental conservation and sustainability practices",
     *     "category": "Science",
     *     "created_at": "2024-01-15T10:00:00.000000Z",
     *     "updated_at": "2024-01-15T10:00:00.000000Z"
     *   }
     * }
     * 
     * @response 404 scenario="Skill not found" {
     *   "message": "Skill not found"
     * }
     * 
     * @return SkillResource
     */
    public function show(Skill $skill)
    {
        return SkillResource::make($skill);
    }
}
