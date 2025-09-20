<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\ApplicationForm;
use App\Models\Comment;
use App\Models\Follow;
use App\Models\Individual;
use App\Models\IndividualPreference;
use App\Models\Like;
use App\Models\Opportunity;
use App\Models\Organization;
use App\Models\Post;
use App\Models\Program;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Seeder;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Spatie\Permission\Models\Role as SpatieRole;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([RoleSeeder::class]);

        User::factory(10)->create();

        $admin = User::factory()->create([
            'name' => 'Admin Joe',
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);
        $admin->assignRole(Role::admin->value);

        $individual = Individual::factory()->for(User::factory()->state([
            'email' => 'individual@example.com',
            'password' => 'password',
        ]))->has(IndividualPreference::factory())
            ->create();
        $individual->user->assignRole(Role::individual->value);

        $organization = Organization::factory()->create();
        $organizer = User::factory()->create([
            'name' => 'Organizer Sam',
            'email' => 'organizer@example.com',
            'password' => 'password',
            'organization_id' => $organization->id,
        ]);
        $organizer->assignRole(Role::organization->value);
        $organizer->organization()->associate($organization)->save();

        Organization::factory(5)
            ->has(
                User::factory()
                    ->state(function (array $attributes, Organization $organization) {
                        return ['organization_id' => $organization->id];
                    })
                    ->hasAttached(SpatieRole::where('name', Role::organization->value)->first())
            )
            ->has(
                Post::factory(3)
                    ->has(Comment::factory(2))
                    ->has(Like::factory(4))
            )
            ->create();

        // Create some follows (users following organizations)
        Follow::factory(10)->create();

        // Create additional comments and likes for posts
        Comment::factory(10)->create();
        Like::factory(10)->create();

        Skill::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        foreach (Organization::all() as $organization) {
            $programs = Program::factory(2)->for($organization)->create();
            foreach ($programs as $program) {
                Opportunity::factory(3)
                    ->for($organization)
                    ->for($program)
                    ->create();
            }
        }

        ApplicationForm::factory(10)->create();
    }
}
