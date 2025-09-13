<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Models\User;
use App\Models\Organization;
use App\Models\Post;
use App\Models\Opportunity;
use App\Models\Follow;
use App\Models\Individual;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_following_feed_returns_posts_from_followed_organizations_for_authenticated_user()
    {
        // Create a user
        /** @var User $user */
        $user = User::factory()->create();
        $user->preferences()->create([
            'language' => 'en',
            'email_notifications' => true,
            'push_notifications' => false,
        ]);

        // Create organizations
        $followedOrg = Organization::factory()->create();
        $notFollowedOrg = Organization::factory()->create();

        // User follows only the first organization
        Follow::create([
            'user_id' => $user->id,
            'followable_id' => $followedOrg->id,
            'followable_type' => Organization::class,
        ]);

        // Create posts
        $followedPost = Post::factory()->create(['organization_id' => $followedOrg->id]);
        $notFollowedPost = Post::factory()->create(['organization_id' => $notFollowedOrg->id]);

        $response = $this->actingAs($user)->getJson('/api/v1/feed/following');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'posts' => [
                    '*' => [
                        'id',
                        'type',
                        'attributes' => [
                            'title',
                            'content',
                        ],
                        'relationships' => [
                            'organization',
                        ],
                    ]
                ],
                'profileCompletion',
            ]);

        // Assert only posts from followed organizations are returned
        $postIds = collect($response->json('posts'))->pluck('id')->toArray();
        $this->assertContains((string)$followedPost->id, $postIds);
        $this->assertNotContains((string)$notFollowedPost->id, $postIds);
    }

    public function test_following_feed_requires_authentication()
    {
        $response = $this->getJson('/api/v1/feed/following');

        $response->assertStatus(401);
    }

    public function test_recent_feed_returns_all_recent_posts_and_opportunities()
    {
        // Clear any existing posts first
        Post::query()->delete();
        Opportunity::query()->delete();

        // Create organizations
        $org1 = Organization::factory()->create();
        $org2 = Organization::factory()->create();

        // Create posts
        $post1 = Post::factory()->create(['organization_id' => $org1->id]);
        $post2 = Post::factory()->create(['organization_id' => $org2->id]);

        // Create opportunities
        $opportunity1 = Opportunity::factory()->create(['organization_id' => $org1->id]);
        $opportunity2 = Opportunity::factory()->create(['organization_id' => $org2->id]);
        $opportunity3 = Opportunity::factory()->create(['organization_id' => $org1->id]);
        $opportunity4 = Opportunity::factory()->create(['organization_id' => $org2->id]); // This should be limited

        $response = $this->getJson('/api/v1/feed/recent');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'posts' => [
                    '*' => [
                        'id',
                        'type',
                        'attributes' => [
                            'title',
                            'content',
                        ],
                        'relationships' => [
                            'organization',
                        ],
                    ]
                ],
                'opportunities' => [
                    '*' => [
                        'id',
                        'type',
                        'title',
                        'description',
                        'status',
                        'organizationId',
                        'organization',
                    ]
                ],
            ]);

        // Assert all posts are returned
        $postIds = collect($response->json('posts'))->pluck('id')->toArray();
        $this->assertContains((string)$post1->id, $postIds);
        $this->assertContains((string)$post2->id, $postIds);

        // Assert opportunities are limited to 3
        $opportunities = $response->json('opportunities');
        $this->assertLessThanOrEqual(3, count($opportunities));

        // Assert no profile completion for unauthenticated request
        $this->assertArrayNotHasKey('profileCompletion', $response->json());
    }

    public function test_following_feed_returns_empty_when_user_follows_no_organizations()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->preferences()->create([
            'language' => 'en',
            'email_notifications' => true,
            'push_notifications' => false,
        ]);
        $individual = Individual::factory()->create(['user_id' => $user->id]);

        // Create some posts and opportunities but user follows no organizations
        Post::factory()->create();
        Opportunity::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/feed/following');

        $response->assertStatus(200)
            ->assertJson([
                'posts' => [],
                'opportunities' => [],
            ])
            ->assertJsonStructure([
                'profileCompletion',
            ]);
    }

    public function test_following_feed_orders_posts_by_latest()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->preferences()->create([
            'language' => 'en',
            'email_notifications' => true,
            'push_notifications' => false,
        ]);
        $individual = Individual::factory()->create(['user_id' => $user->id]);
        $organization = Organization::factory()->create();

        // User follows organization
        Follow::create([
            'user_id' => $user->id,
            'followable_id' => $organization->id,
            'followable_type' => Organization::class,
        ]);

        // Create posts with different timestamps
        $oldPost = Post::factory()->create([
            'organization_id' => $organization->id,
            'created_at' => now()->subDays(2),
        ]);
        $newPost = Post::factory()->create([
            'organization_id' => $organization->id,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($user)->getJson('/api/v1/feed/following');

        $response->assertStatus(200);

        $posts = $response->json('posts');
        $this->assertEquals((string)$newPost->id, $posts[0]['id']);
        $this->assertEquals((string)$oldPost->id, $posts[1]['id']);
    }

    public function test_recent_feed_orders_posts_by_latest()
    {
        $organization = Organization::factory()->create();

        // Create posts with different timestamps - ensure they don't conflict with existing posts
        $oldPost = Post::factory()->create([
            'organization_id' => $organization->id,
            'created_at' => now()->subDays(2),
        ]);
        $newPost = Post::factory()->create([
            'organization_id' => $organization->id,
            'created_at' => now()->subMinutes(1), // More specific timing
        ]);

        $response = $this->getJson('/api/v1/feed/recent');

        $response->assertStatus(200);

        $posts = $response->json('posts');

        // Find our specific posts in the response
        $newPostInResponse = collect($posts)->firstWhere('id', (string)$newPost->id);
        $oldPostInResponse = collect($posts)->firstWhere('id', (string)$oldPost->id);

        $this->assertNotNull($newPostInResponse, 'New post should be in response');
        $this->assertNotNull($oldPostInResponse, 'Old post should be in response');

        // Find their positions
        $newPostIndex = collect($posts)->search(fn($post) => $post['id'] === (string)$newPost->id);
        $oldPostIndex = collect($posts)->search(fn($post) => $post['id'] === (string)$oldPost->id);

        $this->assertLessThan($oldPostIndex, $newPostIndex, 'New post should come before old post');
    }
}
