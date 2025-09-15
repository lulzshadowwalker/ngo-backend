<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Models\Location;
use App\Models\Skill;
use App\Models\User;
use App\Models\VolunteeringInterest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Testing\File;
use Tests\TestCase;
use Tests\Traits\WithRoles;

class RegisterIndividualControllerTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    public function test_it_registers_an_individual(): void
    {
        $skills = Skill::factory()->count(3)->create();
        $volunteeringInterests = VolunteeringInterest::factory()->count(2)->create();
        $location = Location::factory()->create();
        $avatar = File::image('avatar.jpg', 200, 200);

        $response = $this->postJson(route('api.v1.auth.register.individuals'), [
            'data' => [
                'attributes' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'phone' => '123-456-7890',
                    'bio' => 'Lorem ipsum dolor sit amet.',
                    'birthdate' => '1990-01-01',
                    'password' => 'password',
                    'avatar' => $avatar,
                ],
                'relationships' => [
                    'location' => [
                        'data' => [
                            'id' => $location->id,
                        ],
                    ],
                    'skills' => [
                        'data' => $skills->map(fn ($skill) => ['id' => $skill->id])->toArray(),
                    ],
                    'volunteeringInterests' => [
                        'data' => $volunteeringInterests->map(fn ($interest) => ['id' => $interest->id])->toArray(),
                    ],
                ],
            ],
        ]);

        $response->assertStatus(201);

        $user = User::first();

        $this->assertNotNull($user);
        $this->assertNotNull($user->individual);
        $this->assertTrue($user->isIndividual);
        $this->assertFalse($user->isOrganizer);
        $this->assertFalse($user->isAdmin);

        $this->assertNotNull($user->avatar);

        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertEquals('123-456-7890', $user->individual->phone);
        $this->assertEquals('Lorem ipsum dolor sit amet.', $user->individual->bio);
        $this->assertEquals('1990-01-01', $user->individual->birthdate->toDateString());
        $this->assertEquals($location->id, $user->individual->location_id);
        $this->assertCount(3, $user->individual->skills);
        $this->assertCount(2, $user->individual->volunteeringInterests);
    }
}
