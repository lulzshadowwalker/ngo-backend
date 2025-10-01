<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Models\Location;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Testing\File;
use Tests\TestCase;
use Tests\Traits\WithRoles;

class RegisterOrganizationControllerTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    public function test_it_registers_an_organization(): void
    {
        $location = Location::factory()->create();
        $sector = Sector::factory()->create();
        $logo = File::image('logo.png');

        $response = $this->postJson(route('api.v1.auth.register.organizations'), [
            'data' => [
                'attributes' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'contactEmail' => 'contact@example.com',
                    'password' => 'password',
                    'website' => 'https://example.com',
                    'logo' => $logo,
                    'bio' => 'We are an example organization.',
                ],
                'relationships' => [
                    'location' => [
                        'data' => [
                            'id' => $location->id,
                        ],
                    ],
                    'sector' => [
                        'data' => [
                            'id' => $sector->id,
                        ],
                    ],
                ],

            ],
        ]);

        $response->assertStatus(201);

        $user = User::first()->fresh();

        $this->assertNotNull($user);
        $this->assertNotNull($user->organization);
        $this->assertTrue($user->isOrganizer);
        $this->assertFalse($user->isAdmin);
        $this->assertFalse($user->isIndividual);

        $this->assertNotNull($user->avatar);

        $this->assertEquals('contact@example.com', $user->organization->contact_email);
        $this->assertEquals('https://example.com', $user->organization->website);
        $this->assertEquals($sector->id, $user->organization->sector->id);
        $this->assertEquals('We are an example organization.', $user->organization->bio);
        $this->assertEquals($location->id, $user->organization->location->id);
    }

    public function test_organization_can_register_with_device_token(): void
    {
        $location = Location::factory()->create();
        $sector = Sector::factory()->create();
        $logo = File::image('logo.png');
        $deviceToken = 'sample_device_token_123';

        $response = $this->postJson(route('api.v1.auth.register.organizations'), [
            'data' => [
                'attributes' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'contactEmail' => 'contact@example.com',
                    'password' => 'password',
                    'website' => 'https://example.com',
                    'logo' => $logo,
                    'bio' => 'We are an example organization.',
                ],
                'relationships' => [
                    'location' => [
                        'data' => [
                            'id' => $location->id,
                        ],
                    ],
                    'sector' => [
                        'data' => [
                            'id' => $sector->id,
                        ],
                    ],
                    'deviceTokens' => [
                        'data' => [
                            'attributes' => [
                                'token' => $deviceToken,
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertStatus(201);

        $user = User::first()->fresh();

        $this->assertNotNull($user);
        $this->assertNotNull($user->organization);
        $this->assertTrue($user->isOrganizer);
        $this->assertFalse($user->isAdmin);
        $this->assertFalse($user->isIndividual);

        $this->assertNotNull($user->avatar);

        $this->assertEquals('contact@example.com', $user->organization->contact_email);
        $this->assertEquals('https://example.com', $user->organization->website);
        $this->assertEquals($sector->id, $user->organization->sector->id);
        $this->assertEquals('We are an example organization.', $user->organization->bio);
        $this->assertEquals($location->id, $user->organization->location->id);
        $this->assertDatabaseHas('device_tokens', [
            'user_id' => $user->id,
            'token' => $deviceToken,
        ]);
    }
}
