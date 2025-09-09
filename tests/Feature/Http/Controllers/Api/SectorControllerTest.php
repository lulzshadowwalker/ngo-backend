<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Sector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SectorControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_all_available_sectors(): void
    {
        Sector::factory()->count(3)->create();

        $this->get(route('api.v1.sectors.index'))
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_it_shows_a_single_sector(): void
    {
        $sector = Sector::factory()->create();

        $this->get(route('api.v1.sectors.show', $sector))
            ->assertOk()
            ->assertJsonPath('data.id', (string) $sector->id)
            ->assertJsonPath('data.attributes.name', $sector->name)
            ->assertJsonPath('data.attributes.description', $sector->description);
    }
}
