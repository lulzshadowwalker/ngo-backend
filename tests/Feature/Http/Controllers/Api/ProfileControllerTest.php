<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Enums\Role;
use App\Models\Individual;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithRoles;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    public function test_it_returns_individual_profile()
    {
        $individual = Individual::factory()->create();
        $individual->user->assignRole(Role::individual->value);
        $this->actingAs($individual->user);

        $response = $this->getJson(route('api.v1.profile.index'));

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'type' => 'individual',
                    'id' => (string) $individual->id,
                    'attributes' => [
                        'name' => $individual->user->name,
                        'email' => $individual->user->email,
                        'avatar' => $individual->user->avatar,
                        'bio' => $individual->bio,
                        'birthdate' => $individual->birthdate,
                    ],
                ],
            ]);
    }
}
