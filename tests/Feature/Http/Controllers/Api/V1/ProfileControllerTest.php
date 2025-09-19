<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Enums\Role;
use App\Models\Individual;
use App\Models\Location;
use App\Models\Organization;
use App\Models\Sector;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Testing\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\WithRoles;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

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
                        'birthdate' => $individual->birthdate?->toISOString(),
                    ],
                ],
            ]);
    }

    public function test_it_returns_organization_profile()
    {
        $organization = Organization::factory()->create();
        /** @var User $user */
        $user = User::factory()->create(['organization_id' => $organization->id]);
        $organization->users()->save($user);
        $user->assignRole(Role::organization->value);
        $this->actingAs($user);

        $response = $this->getJson(route('api.v1.profile.index'));

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'type' => 'organization',
                    'id' => (string) $organization->id,
                    'attributes' => [
                        'name' => $organization->name,
                        'bio' => $organization->bio,
                        'website' => $organization->website,
                        'contactEmail' => $organization->contact_email,
                        'slug' => $organization->slug,
                    ],
                ],
            ]);
    }

    public function test_it_updates_individual_profile_name_and_bio()
    {
        $individual = Individual::factory()->create();
        $individual->user->assignRole(Role::individual->value);
        $this->actingAs($individual->user);

        $response = $this->patchJson(route('api.v1.profile.update'), [
            'data' => [
                'attributes' => [
                    'name' => 'Updated Name',
                    'bio' => 'Updated bio text',
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'type' => 'individual',
                    'attributes' => [
                        'name' => 'Updated Name',
                        'bio' => 'Updated bio text',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $individual->user->id,
            'name' => 'Updated Name',
        ]);

        $this->assertDatabaseHas('individuals', [
            'id' => $individual->id,
            'bio->en' => 'Updated bio text',
        ]);
    }

    public function test_it_updates_individual_profile_with_location()
    {
        $individual = Individual::factory()->create();
        $individual->user->assignRole(Role::individual->value);
        $location = Location::factory()->create();
        $this->actingAs($individual->user);

        $response = $this->patchJson(route('api.v1.profile.update'), [
            'data' => [
                'relationships' => [
                    'location' => [
                        'data' => ['id' => $location->id],
                    ],
                ],
            ],
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('individuals', [
            'id' => $individual->id,
            'location_id' => $location->id,
        ]);
    }

    public function test_it_updates_individual_profile_with_skills()
    {
        $individual = Individual::factory()->create();
        $individual->user->assignRole(Role::individual->value);
        $skills = Skill::factory(3)->create();
        $this->actingAs($individual->user);

        $response = $this->patchJson(route('api.v1.profile.update'), [
            'data' => [
                'relationships' => [
                    'skills' => [
                        'data' => [
                            ['id' => $skills[0]->id],
                            ['id' => $skills[1]->id],
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertOk();

        $this->assertTrue($individual->skills()->whereIn('id', [$skills[0]->id, $skills[1]->id])->exists());
        $this->assertFalse($individual->skills()->where('id', $skills[2]->id)->exists());
    }

    public function test_it_updates_individual_profile_with_avatar()
    {
        Storage::fake('local');

        $location = Location::factory()->create();
        /** @var User $user */
        $user = User::factory()->create();
        $individual = Individual::factory()->create(['user_id' => $user->id]);
        $user->assignRole(Role::individual->value);
        $this->actingAs($user);

        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->post(route('api.v1.profile.update'), [
            'avatar' => $file,
        ], ['X-HTTP-Method-Override' => 'PATCH']);

        $response->assertOk();
        $this->assertTrue($user->fresh()->getMedia(User::MEDIA_COLLECTION_AVATAR)->isNotEmpty());
    }

    public function test_it_updates_organization_profile_name_and_bio()
    {
        $organization = Organization::factory()->create();
        /** @var User $user */
        $user = User::factory()->create(['organization_id' => $organization->id]);
        $organization->users()->save($user);
        $user->assignRole(Role::organization->value);
        $this->actingAs($user);

        $response = $this->patchJson(route('api.v1.profile.update'), [
            'data' => [
                'attributes' => [
                    'name' => 'Updated Org Name',
                    'bio' => 'Updated organization bio',
                    'website' => 'https://updated-website.com',
                    'contactEmail' => 'updated@contact.com',
                ],
            ],
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Org Name',
        ]);

        $this->assertDatabaseHas('organizations', [
            'id' => $organization->id,
            'name' => 'Updated Org Name',
            'bio' => 'Updated organization bio',
            'website' => 'https://updated-website.com',
            'contact_email' => 'updated@contact.com',
        ]);
    }

    public function test_it_updates_organization_profile_with_location_and_sector()
    {
        $organization = Organization::factory()->create();
        /** @var User $user */
        $user = User::factory()->create(['organization_id' => $organization->id]);
        $organization->users()->save($user);
        $user->assignRole(Role::organization->value);
        $location = Location::factory()->create();
        $sector = Sector::factory()->create();
        $this->actingAs($user);

        $response = $this->patchJson(route('api.v1.profile.update'), [
            'data' => [
                'relationships' => [
                    'location' => [
                        'data' => ['id' => $location->id],
                    ],
                    'sector' => [
                        'data' => ['id' => $sector->id],
                    ],
                ],
            ],
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('organizations', [
            'id' => $organization->id,
            'location_id' => $location->id,
            'sector_id' => $sector->id,
        ]);
    }

    // public function test_it_updates_organization_profile_with_logo()
    // {
    //     Storage::fake('public');

    //     $organization = Organization::factory()->create();
    //     /** @var User $user */
    //     $user = User::factory()->create(['organization_id' => $organization->id]);
    //     $organization->users()->save($user);
    //     $user->assignRole(Role::organization->value);
    //     $this->actingAs($user);

    //     $file = File::image('logo.png');

    //     $response = $this->post(route('api.v1.profile.update'), [
    //         'logo' => $file,
    //     ], ['X-HTTP-Method-Override' => 'PATCH']);

    //     $response->assertOk();
    //     // TODO: Fix media library file handling in tests
    //     // $this->assertTrue($user->fresh()->getMedia(User::MEDIA_COLLECTION_AVATAR)->isNotEmpty());
    //     // $this->assertTrue($organization->fresh()->getMedia(Organization::MEDIA_COLLECTION_LOGO)->isNotEmpty());
    // }

    public function test_it_validates_email_uniqueness()
    {
        $individual = Individual::factory()->create();
        $individual->user->assignRole(Role::individual->value);
        $existingUser = User::factory()->create(['email' => 'existing@test.com']);
        $this->actingAs($individual->user);

        $response = $this->patchJson(route('api.v1.profile.update'), [
            'data' => [
                'attributes' => [
                    'email' => 'existing@test.com',
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['data.attributes.email']);
    }

    public function test_it_validates_image_file_types()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $individual = Individual::factory()->create(['user_id' => $user->id]);
        $user->assignRole(Role::individual->value);
        $this->actingAs($user);

        $file = UploadedFile::fake()->create('document.pdf');

        $response = $this->post(route('api.v1.profile.update'), [
            'avatar' => $file,
        ], ['X-HTTP-Method-Override' => 'PATCH']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['avatar']);
    }

    public function test_it_requires_authentication()
    {
        $response = $this->patchJson(route('api.v1.profile.update'), [
            'data' => [
                'attributes' => [
                    'name' => 'Updated Name',
                ],
            ],
        ]);

        $response->assertStatus(401);
    }

    public function test_it_validates_website_format_for_organizations()
    {
        $organization = Organization::factory()->create();
        /** @var User $user */
        $user = User::factory()->create(['organization_id' => $organization->id]);
        $organization->users()->save($user);
        $user->assignRole(Role::organization->value);
        $this->actingAs($user);

        $response = $this->patchJson(route('api.v1.profile.update'), [
            'data' => [
                'attributes' => [
                    'website' => 'not-a-valid-url',
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['data.attributes.website']);
    }

    public function test_it_validates_contact_email_format_for_organizations()
    {
        $organization = Organization::factory()->create();
        /** @var User $user */
        $user = User::factory()->create(['organization_id' => $organization->id]);
        $organization->users()->save($user);
        $user->assignRole(Role::organization->value);
        $this->actingAs($user);

        $response = $this->patchJson(route('api.v1.profile.update'), [
            'data' => [
                'attributes' => [
                    'contactEmail' => 'not-a-valid-email',
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['data.attributes.contactEmail']);
    }
}
