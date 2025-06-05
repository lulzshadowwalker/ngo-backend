<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_properly_creates_a_slug_for_organization(): void
    {
        $organization = \App\Models\Organization::factory()->create([
            'name' => 'Test Organization 123 -#$%&*()',
        ]);

        $this->assertEquals('testorganization123', $organization->slug);
    }

    public function test_slugs_are_unique(): void
    {
        $organization1 = \App\Models\Organization::factory()->create([
            'name' => 'Unique Organization',
        ]);

        $organization2 = \App\Models\Organization::factory()->create([
            'name' => 'Unique Organization',
        ]);

        $this->assertNotEquals($organization1->slug, $organization2->slug);
    }
}
