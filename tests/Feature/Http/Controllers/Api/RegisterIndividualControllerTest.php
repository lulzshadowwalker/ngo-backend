<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Testing\File;
use Tests\TestCase;
use Tests\Traits\WithRoles;

class RegisterIndividualControllerTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    public function test_it_registers_an_individual(): void
    {
        $location = Location::factory()->create();
        $avatar = File::image('avatar.jpg', 200, 200);

        $response = $this->postJson(route('api.v1.auth.register.individuals'), [
            'data' => [
                'attributes' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'password' => 'password',
                    'avatar' => $avatar,
                ],
                'relationships' => [
                    'location' => [
                        'data' => [
                            'id' => $location->id,
                        ],
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
        $this->assertFileExists($user->avatarFile?->getPath() ?? '');
    }
}
