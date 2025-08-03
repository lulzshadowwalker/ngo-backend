<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Resources\SkillResource;
use App\Models\Skill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SkillControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_skills()
    {
        $skills = Skill::factory()->count(3)->create();
        $resource = SkillResource::collection($skills);

        $response = $this->getJson(route('api.skills.index'));

        $response->assertOk();
        $response->assertJson($resource->response()->getData(true));
    }

    public function test_it_shows_skill()
    {
        $skill = Skill::factory()->create();
        $resource = SkillResource::make($skill);

        $response = $this->getJson(route('api.skills.show', [
            'skill' => $skill->id,
        ]));

        $response->assertOk();
        $response->assertJson($resource->response()->getData(true));
    }
}
