<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Skill;
use App\Models\User;
use App\Models\Organization;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Follow;
use App\Models\Individual;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)->create();

        $individual = Individual::factory()->for(User::factory()->state([
            'email' => 'individual@example.com',
            'password' => 'password',
        ]))->create();
        $individual->user->assignRole(Role::individual->value);

        Organization::factory(5)
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
            "name" => "Test User",
            "email" => "test@example.com",
        ]);
    }
}
